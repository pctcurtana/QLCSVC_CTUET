<?php

namespace App\Services;

use App\Imports\CoSoImport;
use App\Imports\KhuNhaImport;
use App\Imports\PhongImport;
use App\Imports\ThietBiImport;
use App\Models\Import;
use App\Jobs\ProcessExcelImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ThongKeSnapshotService;

/**
 * Service điều phối toàn bộ quá trình Import Excel.
 *
 * Mỗi method nhận UploadedFile hoặc đường dẫn string, khởi tạo Import class tương ứng,
 * gọi Maatwebsite Excel để đọc + xử lý file, sau đó trả về kết quả.
 *
 * Lưu ý: Transaction được quản lý per-row trong BaseImport,
 * không cần wrap thêm transaction ở đây.
 */
class ImportService
{
    /**
     * Khởi tạo lượt import mới: cleanup stale, kiểm tra active, lưu file, tạo record, dispatch job.
     *
     * @param string $module Tên module (co_so, khu_nha, phong, thiet_bi)
     * @param UploadedFile $file File Excel được upload
     * @return Import Bản ghi import vừa tạo
     * @throws \RuntimeException Nếu đang có import khác đang chạy
     */
    public function startImport(string $module, UploadedFile $file): Import
    {
        Import::cleanupStaleImports();

        $hasActive = Import::whereIn('status', ['pending', 'processing'])->exists();
        if ($hasActive) {
            throw new \RuntimeException('Hệ thống đang xử lý một lượt import khác. Vui lòng chờ cho đến khi hoàn tất.');
        }

        $originalName = $file->getClientOriginalName();
        $filePath = $file->store('imports/tmp', 'local');
        $import = Import::create([
            'user_id'           => auth()->id(),
            'module'            => $module,
            'file_path'         => $filePath,
            'original_filename' => $originalName,
            'status'            => 'pending',
        ]);

        ProcessExcelImport::dispatch($import->id, $module)->onQueue('imports');

        return $import;
    }

    /**
     * Import Cơ sở từ file Excel.
     *
     * @param  UploadedFile|string $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importCoSo($file): array
    {
        $startTime = microtime(true);
        $import = new CoSoImport();
        Excel::import($import, $file);
        $import->setExecutionTime(microtime(true) - $startTime);
        $result = $import->getResult();

        // Xóa cache Select và cập nhật snapshot 1 lần duy nhất sau khi toàn bộ import hoàn tất
        app(CoSoService::class)->clearSelectCache();
        app(ThongKeSnapshotService::class)->onEntityChanged('co_so');

        return $result;
    }

    /**
     * Import Khu nhà từ file Excel.
     *
     * @param  UploadedFile|string $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importKhuNha($file): array
    {
        $startTime = microtime(true);
        $import = new KhuNhaImport();
        Excel::import($import, $file);
        $import->setExecutionTime(microtime(true) - $startTime);
        $result = $import->getResult();

        app(KhuNhaService::class)->clearSelectCache();
        app(ThongKeSnapshotService::class)->onEntityChanged('khu_nha');

        return $result;
    }

    /**
     * Import Phòng từ file Excel.
     *
     * @param  UploadedFile|string $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importPhong($file): array
    {
        $startTime = microtime(true);
        $import = new PhongImport();
        Excel::import($import, $file);
        $import->setExecutionTime(microtime(true) - $startTime);
        $result = $import->getResult();

        app(PhongService::class)->clearSelectCache();
        app(ThongKeSnapshotService::class)->onEntityChanged('phong');

        return $result;
    }

    /**
     * Import Thiết bị từ file Excel.
     *
     * @param  UploadedFile|string $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importThietBi($file): array
    {
        $startTime = microtime(true);
        $import = new ThietBiImport();
        Excel::import($import, $file);
        $import->setExecutionTime(microtime(true) - $startTime);
        $result = $import->getResult();

        app(ThongKeSnapshotService::class)->onEntityChanged('thiet_bi');

        return $result;
    }
}

