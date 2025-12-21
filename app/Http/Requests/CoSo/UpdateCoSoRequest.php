<?php

namespace App\Http\Requests\CoSo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoSoRequest extends FormRequest
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
        $coSoId = $this->route('co_so');

        return [
            'ma_co_so' => 'required|string|unique:co_sos,ma_co_so,' . $coSoId,
            'ten_co_so' => 'required|string|max:255',
            'dia_chi' => 'required|string',
            'dien_tich_dat' => 'required|numeric|min:0',
            'vi_tri_khuon_vien' => 'required|numeric|min:0',
            // dien_tich_quy_doi được tính tự động ở backend
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
            'dien_tich_dat' => 'Diện tích đất',
            'vi_tri_khuon_vien' => 'Vị trí khuôn viên',
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
            'dien_tich_dat.min' => 'Diện tích đất phải lớn hơn hoặc bằng 0.',
            'vi_tri_khuon_vien.min' => 'Vị trí khuôn viên phải lớn hơn hoặc bằng 0.',
        ];
    }
}

