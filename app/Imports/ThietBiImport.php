<?php

namespace App\Imports;

use App\Models\Phong;
use App\Models\ThietBi;
use Carbon\Carbon;

/**
 * Import Excel cho module Thiết bị.
 *
 * Cột Excel bắt buộc:
 *   ma_thiet_bi, ma_phong, ten_thiet_bi, loai_thiet_bi, trang_thai
 * Cột tùy chọn:
 *   serial_number, hang_san_xuat, model, nam_san_xuat, nam_mua, ngay_mua,
 *   ngay_bao_duong_cuoi, chu_ky_bao_duong, gia_tri,
 *   thong_so_ky_thuat, mo_ta
 *
 * FK mapping:
 *   ma_phong → phong_id (preloaded từ bảng phongs, chỉ lấy hien_hanh)
 *
 * Khóa nhận diện: ma_thiet_bi
 * so_luong luôn = 1, don_vi_tinh = 'cái' (không import từ Excel)
 * qr_token được tự sinh trong Model::boot() khi creating
 * ngay_bao_duong_tiep_theo được tự tính từ ngay_bao_duong_cuoi/ngay_mua + chu_ky
 */
class ThietBiImport extends BaseImport
{
    protected function getUniqueKey(): string
    {
        return 'ma_thiet_bi';
    }

    protected function getModelClass(): string
    {
        return ThietBi::class;
    }

    /**
     * Preload map: ma_phong → id (chỉ bản ghi hien_hanh).
     */
    protected function prepareReferenceMaps(): void
    {
        $this->referenceMaps['phong_map'] = Phong::where('trang_thai_du_lieu', 'hien_hanh')
            ->pluck('id', 'ma_phong');
    }

    protected function validationRules(): array
    {
        return [
            'ma_thiet_bi'        => 'required|string|max:50',
            'ma_phong'           => ['required', 'string', function ($attribute, $value, $fail) {
                if (!isset($this->referenceMaps['phong_map'][$value])) {
                    $fail("Mã phòng '{$value}' không tồn tại hoặc không còn hiệu lực trong hệ thống.");
                }
            }],
            'serial_number'      => 'required|string|max:100',
            'ten_thiet_bi'       => 'required|string|max:255',
            'loai_thiet_bi'      => 'required|in:van_phong,day_hoc,thuc_hanh,thi_nghiem',
            'hang_san_xuat'      => 'nullable|string|max:100',
            'model'              => 'nullable|string|max:100',
            'nam_mua'            => 'nullable|integer|min:1900|max:' . date('Y'),
            'nam_san_xuat'       => 'nullable|integer|min:1900|max:' . date('Y'),
            'ngay_mua'           => 'required|date_format:Y-m-d',
            'ngay_bao_duong_cuoi' => 'nullable|date_format:Y-m-d',
            'chu_ky_bao_duong'   => 'nullable|integer|min:1',
            'gia_tri'            => 'required|numeric|min:0',
            'thong_so_ky_thuat'  => 'nullable|string',
            'mo_ta'              => 'nullable|string',
            'trang_thai'         => 'required|in:tot,can_sua_chua,hu_hong',
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'ma_thiet_bi.required'  => 'Mã thiết bị không được để trống.',
            'ma_phong.required'     => 'Mã phòng không được để trống.',
            'ten_thiet_bi.required' => 'Tên thiết bị không được để trống.',
            'loai_thiet_bi.required' => 'Loại thiết bị không được để trống.',
            'loai_thiet_bi.in'      => 'Loại thiết bị phải là van_phong, day_hoc, thuc_hanh, thi_nghiem.',
            'serial_number.required' => 'Số serial không được để trống.',
            'ngay_mua.required'     => 'Ngày mua không được để trống.',
            'gia_tri.required'      => 'Giá trị không được để trống.',
            'nam_mua.integer'       => 'Năm mua phải là số nguyên.',
            'nam_san_xuat.integer'  => 'Năm sản xuất phải là số nguyên.',
            'nam_san_xuat.min'      => 'Năm sản xuất phải từ năm 1900.',
            'nam_san_xuat.max'      => 'Năm sản xuất không được vượt quá năm hiện tại (' . date('Y') . ').',
            'ngay_mua.date_format'  => 'Ngày mua phải đúng định dạng Y-m-d (ví dụ: 2024-01-15).',
            'ngay_bao_duong_cuoi.date_format' => 'Ngày bảo dưỡng cuối phải đúng định dạng Y-m-d.',
            'chu_ky_bao_duong.integer' => 'Chu kỳ bảo dưỡng phải là số nguyên (đơn vị: tháng).',
            'gia_tri.numeric'       => 'Giá trị phải là số.',
            'gia_tri.min'           => 'Giá trị phải >= 0.',
            'trang_thai.required'   => 'Trạng thái không được để trống.',
            'trang_thai.in'         => 'Trạng thái phải là: tot, can_sua_chua hoặc hu_hong.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'ma_thiet_bi'         => 'Mã thiết bị',
            'ma_phong'            => 'Mã phòng',
            'serial_number'       => 'Số serial',
            'ten_thiet_bi'        => 'Tên thiết bị',
            'loai_thiet_bi'       => 'Loại thiết bị',
            'hang_san_xuat'       => 'Hãng sản xuất',
            'model'               => 'Model',
            'nam_mua'             => 'Năm mua',
            'nam_san_xuat'        => 'Năm sản xuất',
            'ngay_mua'            => 'Ngày mua',
            'ngay_bao_duong_cuoi' => 'Ngày bảo dưỡng cuối',
            'chu_ky_bao_duong'    => 'Chu kỳ bảo dưỡng',
            'gia_tri'             => 'Giá trị',
            'thong_so_ky_thuat'   => 'Thông số kỹ thuật',
            'mo_ta'               => 'Mô tả',
            'trang_thai'          => 'Trạng thái',
        ];
    }

    /**
     * Map dữ liệu từ dòng Excel sang data array cho ThietBi.
     * Map ma_phong → phong_id từ preloaded map.
     * Tự tính ngay_bao_duong_tiep_theo.
     * Force so_luong = 1, don_vi_tinh = 'cái'.
     */
    protected function mapData(array $row): array
    {
        $phongId = $this->referenceMaps['phong_map'][$this->str($row['ma_phong'])] ?? null;

        // Parse ngày từ Excel (hỗ trợ nhiều định dạng)
        $ngayMua            = $this->parseDate($row['ngay_mua'] ?? null);
        $ngayBaoDuongCuoi   = $this->parseDate($row['ngay_bao_duong_cuoi'] ?? null);
        $chuKyBaoDuong      = isset($row['chu_ky_bao_duong']) && $row['chu_ky_bao_duong'] !== ''
                                ? (int) $row['chu_ky_bao_duong']
                                : null;

        // Tự tính ngày bảo dưỡng tiếp theo
        $ngayBaoDuongTiepTheo = $this->calculateNextMaintenanceDate(
            $ngayBaoDuongCuoi,
            $ngayMua,
            $chuKyBaoDuong
        );

        return [
            'phong_id'                => $phongId,
            'ma_thiet_bi'             => $this->str($row['ma_thiet_bi']),
            'serial_number'           => $this->str($row['serial_number'] ?? null),
            'ten_thiet_bi'            => $this->str($row['ten_thiet_bi']),
            'loai_thiet_bi'           => $this->str($row['loai_thiet_bi']),
            'hang_san_xuat'           => $this->str($row['hang_san_xuat'] ?? null),
            'model'                   => $this->str($row['model'] ?? null),
            'nam_mua'                 => isset($row['nam_mua']) && $row['nam_mua'] !== '' ? (int) $row['nam_mua'] : null,
            'nam_san_xuat'            => isset($row['nam_san_xuat']) && $row['nam_san_xuat'] !== '' ? (int) $row['nam_san_xuat'] : null,
            'ngay_mua'                => $ngayMua,
            'ngay_bao_duong_cuoi'     => $ngayBaoDuongCuoi,
            'chu_ky_bao_duong'        => $chuKyBaoDuong,
            'ngay_bao_duong_tiep_theo' => $ngayBaoDuongTiepTheo,
            'gia_tri'                 => isset($row['gia_tri']) && $row['gia_tri'] !== '' ? (float) $row['gia_tri'] : null,
            'so_luong'                => 1,       // Luôn = 1 (mỗi record = 1 máy)
            'don_vi_tinh'             => 'cái',   // Không cho import từ Excel
            'thong_so_ky_thuat'       => $this->str($row['thong_so_ky_thuat'] ?? null),
            'mo_ta'                   => $this->str($row['mo_ta'] ?? null),
            'trang_thai'              => $this->str($row['trang_thai']),
            'trang_thai_du_lieu'      => 'hien_hanh',
            // qr_token tự sinh trong ThietBi::boot() khi creating
        ];
    }

    /**
     * Tính ngày bảo dưỡng tiếp theo.
     * Logic:
     *   1. Có ngay_bao_duong_cuoi + chu_ky → cộng từ ngày bảo dưỡng cuối
     *   2. Có ngay_mua + chu_ky (chưa bảo dưỡng) → cộng từ ngày mua
     *   3. Không đủ điều kiện → null
     */
    protected function calculateNextMaintenanceDate(
        ?string $ngayBaoDuongCuoi,
        ?string $ngayMua,
        ?int $chuKyBaoDuong
    ): ?string {
        if (!$chuKyBaoDuong) {
            return null;
        }

        if ($ngayBaoDuongCuoi) {
            return Carbon::parse($ngayBaoDuongCuoi)->addMonths($chuKyBaoDuong)->format('Y-m-d');
        }

        if ($ngayMua) {
            return Carbon::parse($ngayMua)->addMonths($chuKyBaoDuong)->format('Y-m-d');
        }

        return null;
    }
}
