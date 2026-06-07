<?php

namespace App\Services;

use App\Imports\CoSoImport;
use App\Imports\KhuNhaImport;
use App\Imports\PhongImport;
use App\Imports\ThietBiImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

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
        $import = new CoSoImport();
        Excel::import($import, $file);
        return $import->getResult();
    }

    /**
     * Import Khu nhà từ file Excel.
     *
     * @param  UploadedFile $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importKhuNha(UploadedFile $file): array
    {
        $import = new KhuNhaImport();
        Excel::import($import, $file);
        return $import->getResult();
    }

    /**
     * Import Phòng từ file Excel.
     *
     * @param  UploadedFile $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importPhong(UploadedFile $file): array
    {
        $import = new PhongImport();
        Excel::import($import, $file);
        return $import->getResult();
    }

    /**
     * Import Thiết bị từ file Excel.
     *
     * @param  UploadedFile $file
     * @return array{total: int, created: int, updated: int, errors: int, error_details: array}
     */
    public function importThietBi(UploadedFile $file): array
    {
        $import = new ThietBiImport();
        Excel::import($import, $file);
        return $import->getResult();
    }
}
