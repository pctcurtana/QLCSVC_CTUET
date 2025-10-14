<?php

namespace App\Http\Requests\Phong;

use Illuminate\Foundation\Http\FormRequest;

class StorePhongRequest extends FormRequest
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
        return [
            'khu_nha_id' => 'required|exists:khu_nhas,id',
            'ma_phong' => 'required|string|unique:phongs,ma_phong',
            'ten_phong' => 'required|string|max:255',
            'loai_phong' => 'required|in:phong_hoc,phong_thi_nghiem,phong_thuc_hanh,phong_lam_viec,phong_chuc_nang',
            'tang' => 'required|integer|min:1',
            'dien_tich' => 'required|numeric|min:0',
            'suc_chua' => 'required|integer|min:0',
            'trang_thiet_bi' => 'nullable|string',
            'mo_ta' => 'nullable|string',
            'trang_thai' => 'required|in:active,maintenance,inactive',
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
            'khu_nha_id' => 'Khu nhà',
            'ma_phong' => 'Mã phòng',
            'ten_phong' => 'Tên phòng',
            'loai_phong' => 'Loại phòng',
            'tang' => 'Tầng',
            'dien_tich' => 'Diện tích',
            'suc_chua' => 'Sức chứa',
            'trang_thiet_bi' => 'Trang thiết bị',
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
            'khu_nha_id.exists' => 'Khu nhà không tồn tại trong hệ thống.',
            'ma_phong.unique' => 'Mã phòng đã tồn tại trong hệ thống.',
            'tang.min' => 'Tầng phải lớn hơn hoặc bằng 1.',
            'suc_chua.min' => 'Sức chứa phải lớn hơn hoặc bằng 0.',
        ];
    }
}

