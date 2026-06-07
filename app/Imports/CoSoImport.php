<?php

namespace App\Imports;

use App\Models\CoSo;

/**
 * Import Excel cho module Cơ sở.
 *
 * Cột Excel bắt buộc:
 *   ma_co_so, ten_co_so, dia_chi, dien_tich_dat, vi_tri_khuon_vien, trang_thai
 * Cột tùy chọn:
 *   mo_ta
 *
 * Khóa nhận diện: ma_co_so
 * Không có FK từ module khác → prepareReferenceMaps() rỗng.
 * Backend tự tính dien_tich_quy_doi = dien_tich_dat * vi_tri_khuon_vien.
 */
class CoSoImport extends BaseImport
{
    protected function getUniqueKey(): string
    {
        return 'ma_co_so';
    }

    protected function getModelClass(): string
    {
        return CoSo::class;
    }

    /**
     * CoSo không phụ thuộc FK nào → không cần preload.
     */
    protected function prepareReferenceMaps(): void
    {
        // Không có FK cần mapping
    }

    protected function validationRules(): array
    {
        return [
            'ma_co_so'          => 'required|string|max:50',
            'ten_co_so'         => 'required|string|max:255',
            'dia_chi'           => 'required|string',
            'dien_tich_dat'     => 'required|numeric|min:0',
            'vi_tri_khuon_vien' => 'required|numeric|min:0',
            'mo_ta'             => 'nullable|string',
            'trang_thai'        => 'required|in:active,inactive',
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'ma_co_so.required'          => 'Mã cơ sở không được để trống.',
            'ten_co_so.required'         => 'Tên cơ sở không được để trống.',
            'dia_chi.required'           => 'Địa chỉ không được để trống.',
            'dien_tich_dat.required'     => 'Diện tích đất không được để trống.',
            'dien_tich_dat.numeric'      => 'Diện tích đất phải là số.',
            'dien_tich_dat.min'          => 'Diện tích đất phải >= 0.',
            'vi_tri_khuon_vien.required' => 'Vị trí khuôn viên không được để trống.',
            'vi_tri_khuon_vien.numeric'  => 'Vị trí khuôn viên phải là số.',
            'vi_tri_khuon_vien.min'      => 'Vị trí khuôn viên phải >= 0.',
            'trang_thai.required'        => 'Trạng thái không được để trống.',
            'trang_thai.in'              => 'Trạng thái phải là active hoặc inactive.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'ma_co_so'          => 'Mã cơ sở',
            'ten_co_so'         => 'Tên cơ sở',
            'dia_chi'           => 'Địa chỉ',
            'dien_tich_dat'     => 'Diện tích đất',
            'vi_tri_khuon_vien' => 'Vị trí khuôn viên',
            'mo_ta'             => 'Mô tả',
            'trang_thai'        => 'Trạng thái',
        ];
    }

    /**
     * Map dữ liệu từ dòng Excel sang data array cho CoSo.
     * Tự động tính dien_tich_quy_doi.
     */
    protected function mapData(array $row): array
    {
        $dienTichDat     = (float) $row['dien_tich_dat'];
        $viTriKhuonVien  = (float) $row['vi_tri_khuon_vien'];
        $dienTichQuyDoi  = $dienTichDat * $viTriKhuonVien;

        return [
            'ma_co_so'           => $this->str($row['ma_co_so']),
            'ten_co_so'          => $this->str($row['ten_co_so']),
            'dia_chi'            => $this->str($row['dia_chi']),
            'dien_tich_dat'      => $dienTichDat,
            'vi_tri_khuon_vien'  => $viTriKhuonVien,
            'dien_tich_quy_doi'  => $dienTichQuyDoi,
            'mo_ta'              => $this->str($row['mo_ta'] ?? null),
            'trang_thai'         => $this->str($row['trang_thai']),
            // Versioning defaults cho bản ghi mới
            'trang_thai_du_lieu' => 'hien_hanh',
        ];
    }
}
