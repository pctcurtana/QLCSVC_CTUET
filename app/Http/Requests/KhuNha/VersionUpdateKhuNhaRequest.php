<?php

namespace App\Http\Requests\KhuNha;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request cho "Lưu phiên bản mới" của toà nhà.
 * Không validate ma_khu_nha (lấy tự động từ bản ghi gốc, không thay đổi).
 */
class VersionUpdateKhuNhaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'co_so_id'              => 'required|exists:co_sos,id',
            'ten_khu_nha'           => 'required|string|max:255',
            'loai_khu_nha'          => 'required|in:phong_hoc,phong_lam_viec,phong_chuc_nang',
            'so_tang'               => 'required|integer|min:1',
            'dien_tich_xay_dung'    => 'required|numeric|min:0',
            'he_so_su_dung_dao_tao' => 'required|numeric|min:0|max:1',
            'nam_xay_dung'          => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'mo_ta'                 => 'nullable|string',
            'trang_thai'            => 'required|in:active,inactive',
        ];
    }

    public function attributes()
    {
        return [
            'co_so_id'              => 'Cơ sở',
            'ten_khu_nha'           => 'Tên toà nhà',
            'loai_khu_nha'          => 'Loại toà nhà',
            'so_tang'               => 'Số tầng',
            'dien_tich_xay_dung'    => 'Diện tích xây dựng',
            'he_so_su_dung_dao_tao' => 'Hệ số DT sử dụng cho đào tạo',
            'nam_xay_dung'          => 'Năm xây dựng',
            'mo_ta'                 => 'Mô tả',
            'trang_thai'            => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'co_so_id.exists'           => 'Cơ sở không tồn tại trong hệ thống.',
            'so_tang.min'               => 'Số tầng phải lớn hơn hoặc bằng 1.',
            'he_so_su_dung_dao_tao.max' => 'Hệ số sử dụng phải nhỏ hơn hoặc bằng 1.',
        ];
    }
}
