<?php

namespace App\Services;

use App\Imports\CoSoImport;
use App\Imports\KhuNhaImport;
use App\Imports\PhongImport;
use App\Imports\ThietBiImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ThongKeSnapshotService;

/**
 * Service điều phối toàn bộ quá trình Import Excel.
 *
 * Mỗi method nhận UploadedFile, khởi tạo Import class tương ứng,
 * gọi Maatwebsite Excel để đọc + xử lý file, sau đó trả về kết quả.
 *
 * Lưu ý: Transaction được quản lý per-row trong BaseImport,
 * không cần wrap thêm transaction ở đây.
 */
class ImportService
{
    /**
     * Import Cơ sở từ file Excel.
     *
     * @param  UploadedFile $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importCoSo(UploadedFile $file): array
    {
        $startTime = microtime(true);
        $import = new CoSoImport();
        Excel::import($import, $file);
        $import->setExecutionTime(microtime(true) - $startTime);
        $result = $import->getResult();

        // Cập nhật snapshot 1 lần duy nhất sau khi toàn bộ import hoàn tất
        app(ThongKeSnapshotService::class)->onEntityChanged('co_so');

        return $result;
    }

    /**
     * Import Khu nhà từ file Excel.
     *
     * @param  UploadedFile $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importKhuNha(UploadedFile $file): array
    {
        $startTime = microtime(true);
        $import = new KhuNhaImport();
        Excel::import($import, $file);
        $import->setExecutionTime(microtime(true) - $startTime);
        $result = $import->getResult();

        app(ThongKeSnapshotService::class)->onEntityChanged('khu_nha');

        return $result;
    }

    /**
     * Import Phòng từ file Excel.
     *
     * @param  UploadedFile $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importPhong(UploadedFile $file): array
    {
        $startTime = microtime(true);
        $import = new PhongImport();
        Excel::import($import, $file);
        $import->setExecutionTime(microtime(true) - $startTime);
        $result = $import->getResult();

        app(ThongKeSnapshotService::class)->onEntityChanged('phong');

        return $result;
    }

    /**
     * Import Thiết bị từ file Excel.
     *
     * @param  UploadedFile $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importThietBi(UploadedFile $file): array
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
