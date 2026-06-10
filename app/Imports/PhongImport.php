<?php

namespace App\Imports;

use App\Models\KhuNha;
use App\Models\Phong;

/**
 * Import Excel cho module Phòng.
 *
 * Cột Excel bắt buộc:
 *   ma_phong, ma_khu_nha, ten_phong, loai_phong, tang, dien_tich, trang_thai
 * Cột tùy chọn:
 *   suc_chua, trang_thiet_bi, mo_ta
 *
 * FK mapping:
 *   ma_khu_nha → khu_nha_id (preloaded từ bảng khu_nhas, chỉ lấy hien_hanh)
 *
 * Khóa nhận diện: ma_phong
 * qr_token được tự sinh trong Model::boot() khi creating → không cần set.
 */
class PhongImport extends BaseImport
{
    protected function getUniqueKey(): string
    {
        return 'ma_phong';
    }

    protected function getModelClass(): string
    {
        return Phong::class;
    }

    /**
     * Preload map: ma_khu_nha → id (chỉ bản ghi hien_hanh).
     */
    protected function prepareReferenceMaps(): void
    {
        $this->referenceMaps['khu_nha_map'] = KhuNha::where('trang_thai_du_lieu', 'hien_hanh')
            ->pluck('id', 'ma_khu_nha');
    }

    protected function validationRules(): array
    {
        return [
            'ma_phong'       => 'required|string|max:50',
            'ma_khu_nha'     => ['required', 'string', function ($attribute, $value, $fail) {
                if (!isset($this->referenceMaps['khu_nha_map'][$value])) {
                    $fail("Mã khu nhà '{$value}' không tồn tại hoặc không còn hiệu lực trong hệ thống.");
                }
            }],
            'ten_phong'      => 'required|string|max:255',
            'loai_phong'     => 'required|in:phong_hoc,phong_thi_nghiem,phong_thuc_hanh,phong_lam_viec,phong_chuc_nang',
            'tang'           => 'required|integer|min:0',
            'dien_tich'      => 'required|numeric|min:0',
            'suc_chua'       => 'required|integer|min:0',
            'trang_thiet_bi' => 'nullable|string',
            'mo_ta'          => 'nullable|string',
            'trang_thai'     => 'required|in:active,maintenance,inactive',
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'ma_phong.required'   => 'Mã phòng không được để trống.',
            'ma_khu_nha.required' => 'Mã khu nhà không được để trống.',
            'ten_phong.required'  => 'Tên phòng không được để trống.',
            'loai_phong.required' => 'Loại phòng không được để trống.',
            'loai_phong.in'       => 'Loại phòng không hợp lệ. Các giá trị cho phép: phong_hoc, phong_thi_nghiem, phong_thuc_hanh, phong_lam_viec, phong_chuc_nang.',
            'tang.required'       => 'Tầng không được để trống.',
            'tang.integer'        => 'Tầng phải là số nguyên.',
            'tang.min'            => 'Tầng phải >= 0 (0 = tầng trệt).',
            'dien_tich.required'  => 'Diện tích không được để trống.',
            'dien_tich.numeric'   => 'Diện tích phải là số.',
            'dien_tich.min'       => 'Diện tích phải >= 0.',
            'suc_chua.integer'    => 'Sức chứa phải là số nguyên.',
            'suc_chua.required'   => 'Sức chứa không được trống.',
            'trang_thai.required' => 'Trạng thái không được để trống.',
            'trang_thai.in'       => 'Trạng thái phải là active, maintenance hoặc inactive.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'ma_phong'       => 'Mã phòng',
            'ma_khu_nha'     => 'Mã khu nhà',
            'ten_phong'      => 'Tên phòng',
            'loai_phong'     => 'Loại phòng',
            'tang'           => 'Tầng',
            'dien_tich'      => 'Diện tích',
            'suc_chua'       => 'Sức chứa',
            'trang_thiet_bi' => 'Trang thiết bị',
            'mo_ta'          => 'Mô tả',
            'trang_thai'     => 'Trạng thái',
        ];
    }

    /**
     * Map dữ liệu từ dòng Excel sang data array cho Phong.
     * Map ma_khu_nha → khu_nha_id từ preloaded map.
     */
    protected function mapData(array $row): array
    {
        $khuNhaId = $this->referenceMaps['khu_nha_map'][$this->str($row['ma_khu_nha'])] ?? null;

        return [
            'khu_nha_id'         => $khuNhaId,
            'ma_phong'           => $this->str($row['ma_phong']),
            'ten_phong'          => $this->str($row['ten_phong']),
            'loai_phong'         => $this->str($row['loai_phong']),
            'tang'               => (int) $row['tang'],
            'dien_tich'          => (float) $row['dien_tich'],
            'suc_chua'           => isset($row['suc_chua']) && $row['suc_chua'] !== '' ? (int) $row['suc_chua'] : null,
            'trang_thiet_bi'     => $this->str($row['trang_thiet_bi'] ?? null),
            'mo_ta'              => $this->str($row['mo_ta'] ?? null),
            'trang_thai'         => $this->str($row['trang_thai']),
            'trang_thai_du_lieu' => 'hien_hanh',
            // qr_token được tự sinh trong Phong::boot() khi creating
        ];
    }
}
