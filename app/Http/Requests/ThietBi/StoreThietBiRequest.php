<?php

namespace App\Http\Requests\ThietBi;

use Illuminate\Foundation\Http\FormRequest;

class StoreThietBiRequest extends FormRequest
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
            'phong_id' => 'nullable|exists:phongs,id',
            'ma_thiet_bi' => 'required|string|unique:thiet_bis,ma_thiet_bi',
            'serial_number' => 'nullable|string',
            'ten_thiet_bi' => 'required|string|max:255',
            'loai_thiet_bi' => 'required|in:van_phong,day_hoc,thi_nghiem,thuc_hanh',
            'hang_san_xuat' => 'nullable|string',
            'model' => 'nullable|string',
            'nam_mua' => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'ngay_mua' => 'nullable|date',
            'chu_ky_bao_duong' => 'nullable|integer|min:1',
            'ngay_bao_duong_cuoi' => 'nullable|date',
            'ghi_chu_bao_duong' => 'nullable|string',
            'gia_tri' => 'required|numeric|min:0',
            'so_luong' => 'required|integer|min:1',
            'don_vi_tinh' => 'required|string',
            'thong_so_ky_thuat' => 'nullable|string',
            'mo_ta' => 'nullable|string',
            'trang_thai' => 'required|in:tot,can_sua_chua,hu_hong',
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
            'phong_id' => 'Phòng',
            'ma_thiet_bi' => 'Mã thiết bị',
            'serial_number' => 'Số serial',
            'ten_thiet_bi' => 'Tên thiết bị',
            'loai_thiet_bi' => 'Loại thiết bị',
            'hang_san_xuat' => 'Hãng sản xuất',
            'model' => 'Model',
            'nam_mua' => 'Năm mua',
            'ngay_mua' => 'Ngày mua',
            'chu_ky_bao_duong' => 'Chu kỳ bảo dưỡng',
            'ngay_bao_duong_cuoi' => 'Ngày bảo dưỡng cuối',
            'ghi_chu_bao_duong' => 'Ghi chú bảo dưỡng',
            'gia_tri' => 'Giá trị',
            'so_luong' => 'Số lượng',
            'don_vi_tinh' => 'Đơn vị tính',
            'thong_so_ky_thuat' => 'Thông số kỹ thuật',
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
            'phong_id.exists' => 'Phòng không tồn tại trong hệ thống.',
            'ma_thiet_bi.unique' => 'Mã thiết bị đã tồn tại trong hệ thống.',
            'so_luong.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
            'chu_ky_bao_duong.min' => 'Chu kỳ bảo dưỡng phải lớn hơn hoặc bằng 1 tháng.',
        ];
    }
}

