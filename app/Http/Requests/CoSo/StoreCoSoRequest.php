<?php

namespace App\Http\Requests\CoSo;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoSoRequest extends FormRequest
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
            'ma_co_so' => 'required|string|unique:co_sos,ma_co_so',
            'ten_co_so' => 'required|string|max:255',
            'dia_chi' => 'required|string',
            'tong_dien_tich' => 'required|numeric|min:0',
            'dien_tich_san_xay_dung' => 'required|numeric|min:0',
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
            'ma_co_so' => 'Mã cơ sở',
            'ten_co_so' => 'Tên cơ sở',
            'dia_chi' => 'Địa chỉ',
            'tong_dien_tich' => 'Tổng diện tích',
            'dien_tich_san_xay_dung' => 'Diện tích sàn xây dựng',
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
            'ma_co_so.unique' => 'Mã cơ sở đã tồn tại trong hệ thống.',
            'tong_dien_tich.min' => 'Tổng diện tích phải lớn hơn hoặc bằng 0.',
            'dien_tich_san_xay_dung.min' => 'Diện tích sàn xây dựng phải lớn hơn hoặc bằng 0.',
        ];
    }
}

