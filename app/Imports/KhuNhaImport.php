<?php

namespace App\Imports;

use App\Models\CoSo;
use App\Models\KhuNha;
use App\Services\KhuNhaService;

/**
 * Import Excel cho module Khu nhà.
 *
 * Cột Excel bắt buộc:
 *   ma_khu_nha, ma_co_so, ten_khu_nha, loai_khu_nha, so_tang,
 *   dien_tich_xay_dung, he_so_su_dung_dao_tao, trang_thai
 * Cột tùy chọn:
 *   nam_xay_dung, mo_ta
 *
 * FK mapping:
 *   ma_co_so → co_so_id (preloaded từ bảng co_sos, chỉ lấy hien_hanh)
 *
 * Khóa nhận diện: ma_khu_nha
 * Backend tự tính:
 *   tong_dien_tich_san = dien_tich_xay_dung × so_tang
 *   dien_tich_san_dao_tao = tong_dien_tich_san × he_so_su_dung_dao_tao
 * Sử dụng hàm calculateDienTich() từ KhuNhaService.
 */
class KhuNhaImport extends BaseImport
{
    protected function getUniqueKey(): string
    {
        return 'ma_khu_nha';
    }

    protected function getModelClass(): string
    {
        return KhuNha::class;
    }

    /**
     * Preload map: ma_co_so → id (chỉ bản ghi hien_hanh).
     * Tránh N+1 query khi xử lý nhiều dòng.
     */
    protected function prepareReferenceMaps(): void
    {
        $this->referenceMaps['co_so_map'] = CoSo::where('trang_thai_du_lieu', 'hien_hanh')
            ->pluck('id', 'ma_co_so');
    }

    protected function validationRules(): array
    {
        return [
            'ma_khu_nha'              => 'required|string|max:50',
            'ma_co_so'                => ['required', 'string', function ($attribute, $value, $fail) {
                if (!isset($this->referenceMaps['co_so_map'][$value])) {
                    $fail("Mã cơ sở '{$value}' không tồn tại hoặc không còn hiệu lực trong hệ thống.");
                }
            }],
            'ten_khu_nha'             => 'required|string|max:255',
            'loai_khu_nha'            => 'required|in:phong_hoc,phong_lam_viec,phong_chuc_nang',
            'so_tang'                 => 'required|integer|min:1',
            'dien_tich_xay_dung'      => 'required|numeric|min:0',
            'he_so_su_dung_dao_tao'   => 'required|numeric|min:0|max:1',
            'nam_xay_dung'            => 'nullable|integer|min:1900|max:' . date('Y'),
            'mo_ta'                   => 'nullable|string',
            'trang_thai'              => 'required|in:active,inactive',
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'ma_khu_nha.required'              => 'Mã khu nhà không được để trống.',
            'ma_co_so.required'                => 'Mã cơ sở không được để trống.',
            'ten_khu_nha.required'             => 'Tên khu nhà không được để trống.',
            'loai_khu_nha.required'            => 'Loại khu nhà không được để trống.',
            'loai_khu_nha.in'                  => 'Loại khu nhà phải là phong_hoc, phong_lam_viec, phong_chuc_nang.',
            'so_tang.required'                 => 'Số tầng không được để trống.',
            'so_tang.integer'                  => 'Số tầng phải là số nguyên.',
            'so_tang.min'                      => 'Số tầng phải >= 1.',
            'dien_tich_xay_dung.required'      => 'Diện tích xây dựng không được để trống.',
            'dien_tich_xay_dung.numeric'       => 'Diện tích xây dựng phải là số.',
            'dien_tich_xay_dung.min'           => 'Diện tích xây dựng phải >= 0.',
            'he_so_su_dung_dao_tao.required'   => 'Hệ số sử dụng đào tạo không được để trống.',
            'he_so_su_dung_dao_tao.numeric'    => 'Hệ số sử dụng đào tạo phải là số.',
            'he_so_su_dung_dao_tao.max'        => 'Hệ số sử dụng đào tạo phải <= 1.',
            'nam_xay_dung.integer'             => 'Năm xây dựng phải là số nguyên.',
            'trang_thai.required'              => 'Trạng thái không được để trống.',
            'trang_thai.in'                    => 'Trạng thái phải là active hoặc inactive.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'ma_khu_nha'              => 'Mã khu nhà',
            'ma_co_so'                => 'Mã cơ sở',
            'ten_khu_nha'             => 'Tên khu nhà',
            'loai_khu_nha'            => 'Loại khu nhà',
            'so_tang'                 => 'Số tầng',
            'dien_tich_xay_dung'      => 'Diện tích xây dựng',
            'he_so_su_dung_dao_tao'   => 'Hệ số sử dụng đào tạo',
            'nam_xay_dung'            => 'Năm xây dựng',
            'mo_ta'                   => 'Mô tả',
            'trang_thai'              => 'Trạng thái',
        ];
    }

    /**
     * Map dữ liệu từ dòng Excel sang data array cho KhuNha.
     * Map ma_co_so → co_so_id từ preloaded map.
     * Sử dụng KhuNhaService::calculateDienTich() để tính tự động
     * tong_dien_tich_san và dien_tich_san_dao_tao.
     */
    protected function mapData(array $row): array
    {
        $coSoId = $this->referenceMaps['co_so_map'][$this->str($row['ma_co_so'])] ?? null;

        $data = [
            'co_so_id'                 => $coSoId,
            'ma_khu_nha'               => $this->str($row['ma_khu_nha']),
            'ten_khu_nha'              => $this->str($row['ten_khu_nha']),
            'loai_khu_nha'             => $this->str($row['loai_khu_nha']),
            'so_tang'                  => (int) $row['so_tang'],
            'dien_tich_xay_dung'       => (float) $row['dien_tich_xay_dung'],
            'he_so_su_dung_dao_tao'    => (float) $row['he_so_su_dung_dao_tao'],
            'nam_xay_dung'             => isset($row['nam_xay_dung']) && $row['nam_xay_dung'] !== '' ? (int) $row['nam_xay_dung'] : null,
            'mo_ta'                    => $this->str($row['mo_ta'] ?? null),
            'trang_thai'               => $this->str($row['trang_thai']),
            'trang_thai_du_lieu'       => 'hien_hanh',
        ];

        // Sử dụng hàm tính toán từ Service để tính tong_dien_tich_san và dien_tich_san_dao_tao
        $khuNhaService = app(KhuNhaService::class);
        $data = $khuNhaService->calculateDienTich($data);

        return $data;
    }
}
