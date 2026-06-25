<?php

namespace App\Http\Requests\ThietBi;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request cho "Lưu phiên bản mới" của thiết bị.
 * Không validate ma_thiet_bi và serial_number
 * (lấy tự động từ bản ghi gốc, không thay đổi).
 */
class VersionUpdateThietBiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phong_id'           => 'nullable|exists:phongs,id',
            'ten_thiet_bi'       => 'required|string|max:255',
            'loai_thiet_bi'      => 'required|in:van_phong,day_hoc,thi_nghiem,thuc_hanh',
            'hang_san_xuat'      => 'nullable|string',
            'model'              => 'nullable|string',
            'nam_mua'            => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'nam_san_xuat'       => 'nullable|integer|min:1900|max:' . date('Y'),
            'ngay_mua'           => 'required|date',
            'chu_ky_bao_duong'   => 'nullable|integer|min:1',
            'ngay_bao_duong_cuoi' => 'nullable|date',
            'ghi_chu_bao_duong'  => 'nullable|string',
            'gia_tri'            => 'required|numeric|min:0',
            'thong_so_ky_thuat'  => 'nullable|string',
            'mo_ta'              => 'nullable|string',
            'trang_thai'         => 'required|in:tot,can_sua_chua,hu_hong',
        ];
    }

    public function attributes()
    {
        return [
            'phong_id'           => 'Phòng',
            'ten_thiet_bi'       => 'Tên thiết bị',
            'loai_thiet_bi'      => 'Loại thiết bị',
            'hang_san_xuat'      => 'Hãng sản xuất',
            'model'              => 'Model',
            'nam_mua'            => 'Năm mua',
            'nam_san_xuat'       => 'Năm sản xuất',
            'ngay_mua'           => 'Ngày mua',
            'chu_ky_bao_duong'   => 'Chu kỳ bảo dưỡng',
            'ngay_bao_duong_cuoi' => 'Ngày bảo dưỡng cuối',
            'ghi_chu_bao_duong'  => 'Ghi chú bảo dưỡng',
            'gia_tri'            => 'Giá trị',
            'thong_so_ky_thuat'  => 'Thông số kỹ thuật',
            'mo_ta'              => 'Mô tả',
            'trang_thai'         => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'phong_id.exists'       => 'Phòng không tồn tại trong hệ thống.',
            'ngay_mua.required'     => 'Ngày mua là bắt buộc để theo dõi chu kỳ bảo dưỡng.',
            'chu_ky_bao_duong.min'  => 'Chu kỳ bảo dưỡng phải lớn hơn hoặc bằng 1 tháng.',
            'nam_san_xuat.integer'  => 'Năm sản xuất phải là số nguyên.',
            'nam_san_xuat.min'      => 'Năm sản xuất phải từ năm 1900.',
            'nam_san_xuat.max'      => 'Năm sản xuất không được vượt quá năm hiện tại (' . date('Y') . ').',
        ];
    }
}
