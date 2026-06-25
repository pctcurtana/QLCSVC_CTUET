<?php

namespace App\Http\Requests\BaoCaoSuCo;

use Illuminate\Foundation\Http\FormRequest;

class StoreBaoCaoSuCoRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Public form - no auth required
    }

    public function rules()
    {
        return [
            'ten_nguoi_bao' => 'required|string|max:100',
            'so_dien_thoai' => 'nullable|string|max:20',
            'thiet_bi_id'   => 'nullable|exists:thiet_bis,id',
            'mo_ta_su_co'   => 'required|string|min:3|max:1000',
            'muc_do'        => 'required|in:thap,trung_binh,cao,khan_cap',
            // Honeypot — must be empty
            'website'       => 'max:0',
        ];
    }

    public function messages()
    {
        return [
            'ten_nguoi_bao.required' => 'Vui lòng nhập tên người báo cáo.',
            'mo_ta_su_co.required'   => 'Vui lòng mô tả sự cố.',
            'mo_ta_su_co.min'        => 'Mô tả quá ngắn, vui lòng nhập ít nhất 3 ký tự.',
            'muc_do.required'        => 'Vui lòng chọn mức độ nghiêm trọng.',
            'website.max'            => 'Phát hiện spam.',
        ];
    }

    public function attributes()
    {
        return [
            'ten_nguoi_bao' => 'Tên người báo cáo',
            'so_dien_thoai' => 'Số điện thoại',
            'mo_ta_su_co'   => 'Mô tả sự cố',
            'muc_do'        => 'Mức độ',
        ];
    }
}
