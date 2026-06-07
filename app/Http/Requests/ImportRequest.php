<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules cho file upload import Excel.
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // tối đa 10MB
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'File Excel',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn file Excel để import.',
            'file.file'     => 'Dữ liệu gửi lên phải là file.',
            'file.mimes'    => 'File phải có định dạng .xlsx hoặc .xls.',
            'file.max'      => 'File không được vượt quá 10MB.',
        ];
    }
}
