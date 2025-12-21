<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
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
            'permissions' => 'required|array',
            'permissions.*.screen_id' => 'required|exists:screens,id',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_edit' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
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
            'permissions' => 'Danh sách quyền',
            'permissions.*.screen_id' => 'ID màn hình',
            'permissions.*.can_view' => 'Quyền xem',
            'permissions.*.can_create' => 'Quyền thêm',
            'permissions.*.can_edit' => 'Quyền sửa',
            'permissions.*.can_delete' => 'Quyền xóa',
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
            'permissions.required' => 'Vui lòng cung cấp danh sách quyền.',
            'permissions.array' => 'Danh sách quyền phải là mảng.',
            'permissions.*.screen_id.required' => 'ID màn hình là bắt buộc.',
            'permissions.*.screen_id.exists' => 'Màn hình không tồn tại trong hệ thống.',
        ];
    }
}

