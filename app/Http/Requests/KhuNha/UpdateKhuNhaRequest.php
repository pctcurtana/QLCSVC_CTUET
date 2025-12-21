<?php

namespace App\Http\Requests\KhuNha;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKhuNhaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $khuNhaId = $this->route('khu_nha');

        return [
            'co_so_id' => 'required|exists:co_sos,id',
            'ma_khu_nha' => 'required|string|unique:khu_nhas,ma_khu_nha,' . $khuNhaId,
            'ten_khu_nha' => 'required|string|max:255',
            'loai_khu_nha' => 'required|in:phong_hoc,phong_lam_viec,phong_chuc_nang',
            'so_tang' => 'required|integer|min:1',
            'tong_dien_tich_san' => 'required|numeric|min:0',
            'he_so_su_dung_dao_tao' => 'required|numeric|min:0|max:1',
            // dien_tich_san_dao_tao được tính tự động ở backend
            'nam_xay_dung' => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'mo_ta' => 'nullable|string',
            'trang_thai' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'co_so_id' => 'Cơ sở',
            'ma_khu_nha' => 'Mã khu nhà',
            'ten_khu_nha' => 'Tên khu nhà',
            'loai_khu_nha' => 'Loại khu nhà',
            'so_tang' => 'Số tầng',
            'tong_dien_tich_san' => 'Tổng diện tích sàn xây dựng',
            'he_so_su_dung_dao_tao' => 'Hệ số DT sử dụng cho đào tạo',
            'nam_xay_dung' => 'Năm xây dựng',
            'mo_ta' => 'Mô tả',
            'trang_thai' => 'Trạng thái',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'co_so_id.exists' => 'Cơ sở không tồn tại trong hệ thống.',
            'ma_khu_nha.unique' => 'Mã khu nhà đã tồn tại trong hệ thống.',
            'so_tang.min' => 'Số tầng phải lớn hơn hoặc bằng 1.',
            'he_so_su_dung_dao_tao.max' => 'Hệ số sử dụng phải nhỏ hơn hoặc bằng 1.',
        ];
    }
}

