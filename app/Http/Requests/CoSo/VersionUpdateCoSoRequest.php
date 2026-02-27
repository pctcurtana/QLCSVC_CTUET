<?php

namespace App\Http\Requests\CoSo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request cho "Lưu phiên bản mới" của cơ sở.
 * Không validate ma_co_so (lấy tự động từ bản ghi gốc, không thay đổi).
 */
class VersionUpdateCoSoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ten_co_so'          => 'required|string|max:255',
            'dia_chi'            => 'required|string',
            'dien_tich_dat'      => 'required|numeric|min:0',
            'vi_tri_khuon_vien'  => 'required|numeric|min:0',
            'mo_ta'              => 'nullable|string',
            'trang_thai'         => 'required|in:active,inactive',
        ];
    }

    public function attributes()
    {
        return [
            'ten_co_so'         => 'Tên cơ sở',
            'dia_chi'           => 'Địa chỉ',
            'dien_tich_dat'     => 'Diện tích đất',
            'vi_tri_khuon_vien' => 'Vị trí khuôn viên',
            'mo_ta'             => 'Mô tả',
            'trang_thai'        => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'dien_tich_dat.min'     => 'Diện tích đất phải lớn hơn hoặc bằng 0.',
            'vi_tri_khuon_vien.min' => 'Vị trí khuôn viên phải lớn hơn hoặc bằng 0.',
        ];
    }
}
