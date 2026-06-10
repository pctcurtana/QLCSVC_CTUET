<?php

namespace App\Imports;

use App\Models\CoSo;
use App\Models\KhuNha;

/**
 * Import Excel cho module Khu nhà.
 *
 * Cột Excel bắt buộc:
 *   ma_khu_nha, ma_co_so, ten_khu_nha, loai_khu_nha, so_tang,
 *   tong_dien_tich_san, he_so_su_dung_dao_tao, trang_thai
 * Cột tùy chọn:
 *   nam_xay_dung, mo_ta
 *
 * FK mapping:
 *   ma_co_so → co_so_id (preloaded từ bảng co_sos, chỉ lấy hien_hanh)
 *
 * Khóa nhận diện: ma_khu_nha
 * Backend tự tính dien_tich_san_dao_tao = tong_dien_tich_san * he_so_su_dung_dao_tao.
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
            'tong_dien_tich_san'      => 'required|numeric|min:0',
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
            'tong_dien_tich_san.required'      => 'Tổng diện tích sàn không được để trống.',
            'tong_dien_tich_san.numeric'       => 'Tổng diện tích sàn phải là số.',
            'tong_dien_tich_san.min'           => 'Tổng diện tích sàn phải >= 0.',
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
            'tong_dien_tich_san'      => 'Tổng diện tích sàn',
            'he_so_su_dung_dao_tao'   => 'Hệ số sử dụng đào tạo',
            'nam_xay_dung'            => 'Năm xây dựng',
            'mo_ta'                   => 'Mô tả',
            'trang_thai'              => 'Trạng thái',
        ];
    }

    /**
     * Map dữ liệu từ dòng Excel sang data array cho KhuNha.
     * Map ma_co_so → co_so_id từ preloaded map.
     * Tự động tính dien_tich_san_dao_tao.
     */
    protected function mapData(array $row): array
    {
        $tongDienTichSan    = (float) $row['tong_dien_tich_san'];
        $heSoSuDungDaoTao   = (float) $row['he_so_su_dung_dao_tao'];
        $dienTichSanDaoTao  = $tongDienTichSan * $heSoSuDungDaoTao;

        $coSoId = $this->referenceMaps['co_so_map'][$this->str($row['ma_co_so'])] ?? null;

        return [
            'co_so_id'                 => $coSoId,
            'ma_khu_nha'               => $this->str($row['ma_khu_nha']),
            'ten_khu_nha'              => $this->str($row['ten_khu_nha']),
            'loai_khu_nha'             => $this->str($row['loai_khu_nha']),
            'so_tang'                  => (int) $row['so_tang'],
            'tong_dien_tich_san'       => $tongDienTichSan,
            'he_so_su_dung_dao_tao'    => $heSoSuDungDaoTao,
            'dien_tich_san_dao_tao'    => $dienTichSanDaoTao,
            'nam_xay_dung'             => isset($row['nam_xay_dung']) && $row['nam_xay_dung'] !== '' ? (int) $row['nam_xay_dung'] : null,
            'mo_ta'                    => $this->str($row['mo_ta'] ?? null),
            'trang_thai'               => $this->str($row['trang_thai']),
            'trang_thai_du_lieu'       => 'hien_hanh',
        ];
    }
}
