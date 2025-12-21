<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phong extends Model
{
    use HasFactory;

    protected $table = 'phongs';

    protected $fillable = [
        'khu_nha_id',
        'ma_phong',
        'ten_phong',
        'loai_phong',
        'tang',
        'dien_tich',
        'suc_chua',
        'trang_thiet_bi',
        'mo_ta',
        'trang_thai',
    ];

    protected $casts = [
        'tang' => 'integer',
        'dien_tich' => 'decimal:2',
        'suc_chua' => 'integer',
    ];

    public function khuNha()
    {
        return $this->belongsTo(KhuNha::class, 'khu_nha_id');
    }

    public function thietBis()
    {
        return $this->hasMany(ThietBi::class, 'phong_id');
    }

    public function getTongSoThietBiAttribute()
    {
        // Đếm số thiết bị (mỗi record = 1 máy)
        return $this->thietBis()->count();
    }

    public function getTongGiaTriThietBiAttribute()
    {
        // Tổng giá trị = tổng gia_tri của tất cả thiết bị
        return $this->thietBis()->sum('gia_tri');
    }
}

