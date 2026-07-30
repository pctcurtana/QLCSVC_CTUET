<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Abstract base class cho tất cả Excel Import.
 *
 * Chiến lược:
 *  - Preload toàn bộ reference maps 1 lần trước vòng lặp (tránh N+1)
 *  - Mỗi dòng trong 1 DB::transaction riêng (lỗi 1 dòng không ảnh hưởng dòng khác)
 *  - Upsert trực tiếp (không tạo version mới)
 *  - Chỉ thao tác với bản ghi trang_thai_du_lieu = 'hien_hanh'
 */
abstract class BaseImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    /** Tổng số dòng đã xử lý (bỏ qua header) */
    protected int $totalRows = 0;

    /** Số dòng tạo mới */
    protected int $createdRows = 0;

    /** Số dòng cập nhật */
    protected int $updatedRows = 0;

    /** Tổng số lỗi (đếm đầy đủ qua tất cả chunk) */
    protected int $errorCount = 0;

    /** Danh sách lỗi chi tiết theo dòng (tối đa MAX_ERROR_DETAILS để tránh tăng RAM) */
    protected array $errors = [];

    /** Số lượng error_details tối đa được giữ lại */
    protected const MAX_ERROR_DETAILS = 200;

    /** Thời gian xử lý thực tế (tính bằng giây) */
    protected ?float $executionTime = null;

    /** Flag đảm bảo prepareReferenceMaps() chỉ chạy 1 lần dù có nhiều chunk */
    protected bool $referenceMapsLoaded = false;

    /**
     * Offset của chunk hiện tại (dòng bắt đầu trong file Excel).
     * Maatwebsite tự gọi setChunkOffset() trước mỗi chunk.
     * Dùng để tính đúng row number trong báo lỗi.
     */
    protected int $chunkOffset = 0;

    /**
     * Maps preloaded để tra cứu FK không cần query lại DB.
     * Ví dụ: ['co_so_map' => Collection<ma_co_so => id>]
     */
    protected array $referenceMaps = [];

    // =========================================================
    // Template Methods — Subclass BẮT BUỘC implement
    // =========================================================

    /**
     * Trả về tên trường mã nghiệp vụ làm khóa nhận diện duy nhất.
     * Ví dụ: 'ma_co_so', 'ma_khu_nha', ...
     */
    abstract protected function getUniqueKey(): string;

    /**
     * Trả về FQCN của Model cần upsert.
     * Ví dụ: \App\Models\CoSo::class
     */
    abstract protected function getModelClass(): string;

    /**
     * Trả về validation rules cho mỗi dòng dữ liệu.
     */
    abstract protected function validationRules(): array;

    /**
     * Trả về custom validation messages.
     */
    abstract protected function validationMessages(): array;

    /**
     * Trả về custom attribute names.
     */
    abstract protected function validationAttributes(): array;

    /**
     * Preload toàn bộ reference maps cần thiết cho việc mapping FK.
     * Được gọi 1 lần duy nhất trước khi xử lý các dòng.
     * Kết quả lưu vào $this->referenceMaps.
     */
    abstract protected function prepareReferenceMaps(): void;

    /**
     * Map dữ liệu từ dòng Excel sang data array để upsert vào DB.
     * Thực hiện:
     *   - Rename/drop columns
     *   - Map ma_xxx → id từ $this->referenceMaps
     *   - Tính toán nghiệp vụ (diện tích, ngày bảo dưỡng, ...)
     *
     * @param array $row Dữ liệu từ 1 dòng Excel (key = heading)
     * @return array Data đã mapping, sẵn sàng để updateOrCreate
     */
    abstract protected function mapData(array $row): array;

    // =========================================================
    // Core Logic
    // =========================================================

    /**
     * Kích thước mỗi chunk khi đọc file Excel.
     * WithChunkReading sẽ đọc file theo từng chunk thay vì load toàn bộ vào RAM.
     * Giá trị 1000 dòng/chunk phù hợp cho file lên tới 100.000+ dòng.
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Được Maatwebsite ReadChunk tự gọi trước mỗi chunk.
     * Lưu lại vị trí dòng bắt đầu để tính đúng row number cho báo lỗi.
     */
    public function setChunkOffset(int $offset): void
    {
        $this->chunkOffset = $offset;
    }

    /**
     * Entry point của Maatwebsite Excel.
     * Được gọi 1 lần cho mỗi chunk (không phải toàn bộ file).
     * Preload maps chỉ 1 lần → xử lý từng dòng trong transaction riêng.
     * Các biến đếm (totalRows, createdRows, updatedRows, errorCount) tự cộng dồn
     * vì WithChunkReading (không ShouldQueue) chạy synchronous trên cùng 1 instance.
     */
    public function collection(Collection $rows): void
    {
        // Chunk đầu tiên: preload FK maps + tắt query log để tiết kiệm RAM
        if (!$this->referenceMapsLoaded) {
            $this->prepareReferenceMaps();
            $this->referenceMapsLoaded = true;
            DB::disableQueryLog();
        }

        foreach ($rows as $index => $row) {
            // chunkOffset là dòng Excel bắt đầu (đã trừ header row),
            // $index là vị trí 0-based trong chunk hiện tại.
            $rowNumber = $this->chunkOffset + $index;
            $rowArray  = $row->toArray();

            // Bỏ qua dòng trống hoàn toàn
            if ($this->isEmptyRow($rowArray)) {
                continue;
            }

            // Bỏ qua dòng ghi chú hướng dẫn của template
            if ($this->isInstructionRow($rowArray)) {
                continue;
            }

            $this->totalRows++;

            // Mỗi dòng trong 1 transaction riêng
            try {
                DB::transaction(function () use ($rowNumber, $rowArray) {
                    $this->processRow($rowNumber, $rowArray);
                });
            } catch (\Throwable $e) {
                $this->addError([
                    'row'     => $rowNumber,
                    'field'   => null,
                    'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                ]);
            }
        }

        // Giải phóng RAM sau mỗi chunk: dọn Eloquent model instances đã hết scope
        gc_collect_cycles();
    }

    /**
     * Xử lý 1 dòng: validate → mapData → upsert.
     */
    protected function processRow(int $rowNumber, array $row): void
    {
        // 1. Validate
        $validationErrors = $this->validateRow($rowNumber, $row);
        if (!empty($validationErrors)) {
            // Lỗi đã được ghi bên trong validateRow()
            return;
        }

        // 2. Map data (FK + business logic)
        try {
            $data = $this->mapData($row);
        } catch (\Throwable $e) {
            $this->addError([
                'row'     => $rowNumber,
                'field'   => null,
                'message' => 'Lỗi mapping dữ liệu: ' . $e->getMessage(),
            ]);
            return;
        }

        // 3. Upsert: updateOrCreate theo khóa nghiệp vụ + hien_hanh
        $uniqueKey   = $this->getUniqueKey();
        $uniqueValue = $row[$uniqueKey] ?? null;
        $modelClass  = $this->getModelClass();

        $record = $modelClass::updateOrCreate(
            [
                $uniqueKey           => $uniqueValue,
                'trang_thai_du_lieu' => 'hien_hanh',
            ],
            $data
        );

        // 4. Đếm kết quả
        if ($record->wasRecentlyCreated) {
            $this->createdRows++;
        } else {
            $this->updatedRows++;
        }
    }

    /**
     * Validate 1 dòng dữ liệu.
     * Lỗi được push vào $this->errors.
     *
     * @return array Danh sách lỗi (rỗng nếu hợp lệ)
     */
    protected function validateRow(int $rowNumber, array $row): array
    {
        $validator = Validator::make(
            $row,
            $this->validationRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        if ($validator->fails()) {
            $rowErrors = [];
            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $rowErrors[] = [
                        'row'     => $rowNumber,
                        'field'   => $field,
                        'message' => $message,
                    ];
                }
            }
            foreach ($rowErrors as $err) {
                $this->addError($err);
            }
            return $rowErrors;
        }

        return [];
    }

    /**
     * Kiểm tra dòng có trống hay không.
     */
    protected function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn($v) => $v !== null && $v !== ''));
    }

    /**
     * Kiểm tra xem dòng có phải dòng ghi chú hướng dẫn của template hay không.
     */
    protected function isInstructionRow(array $row): bool
    {
        foreach ($row as $val) {
            if (is_string($val) && (str_contains($val, 'BẮT BUỘC |') || str_contains($val, 'Tùy chọn |'))) {
                return true;
            }
        }
        return false;
    }

    // =========================================================
    // Helpers cho Subclass
    // =========================================================

    /**
     * Lấy giá trị string, trim whitespace, null nếu rỗng.
     */
    protected function str(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Lấy giá trị numeric, null nếu không hợp lệ.
     */
    protected function num($value): ?float
    {
        if ($value === null || $value === '') return null;
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Parse ngày tháng từ nhiều định dạng khác nhau.
     * Hỗ trợ: Y-m-d, d/m/Y, d-m-Y, Excel serial number.
     */
    protected function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        // Excel serial number (số nguyên dương)
        if (is_numeric($value) && (int) $value > 0 && (int) $value < 100000) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value);
                return $date->format('Y-m-d');
            } catch (\Throwable $e) {
                // fall through
            }
        }

        // Các định dạng string thông dụng
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, (string) $value);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    // =========================================================
    // Error Tracking (giới hạn RAM)
    // =========================================================

    /**
     * Thêm 1 lỗi vào danh sách.
     * - errorCount luôn tăng (đếm đầy đủ).
     * - error_details chỉ giữ tối đa MAX_ERROR_DETAILS mục để tránh tăng RAM.
     */
    protected function addError(array $error): void
    {
        $this->errorCount++;
        if (count($this->errors) < static::MAX_ERROR_DETAILS) {
            $this->errors[] = $error;
        }
    }

    // =========================================================
    // Kết quả Import
    // =========================================================

    /**
     * Set thời gian xử lý import (tính bằng giây).
     */
    public function setExecutionTime(float $seconds): void
    {
        $this->executionTime = round($seconds, 2);
    }

    /**
     * Trả về tổng kết quả import.
     */
    public function getResult(): array
    {
        $result = [
            'total'         => $this->totalRows,
            'created'       => $this->createdRows,
            'updated'       => $this->updatedRows,
            'errors'        => $this->errorCount,
            'error_details'  => $this->errors,
        ];

        if ($this->executionTime !== null) {
            $result['execution_time'] = $this->executionTime;
        }

        return $result;
    }
}
