<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhuNha extends Model
{
    use HasFactory;

    protected $table = 'khu_nhas';

    protected $fillable = [
        'co_so_id',
        'ma_khu_nha',
        'ten_khu_nha',
        'loai_khu_nha',
        'so_tang',
        'tong_dien_tich_san',       // Tổng diện tích sàn xây dựng (m²) - nhập tay
        'he_so_su_dung_dao_tao',    // Hệ số DT sử dụng cho đào tạo - mặc định 0.7
        'dien_tich_san_dao_tao',    // DT sàn sử dụng cho đào tạo = tong_dien_tich_san * he_so_su_dung_dao_tao
        'nam_xay_dung',
        'mo_ta',
        'trang_thai',
    ];

    protected $casts = [
        'so_tang' => 'integer',
        'nam_xay_dung' => 'integer',
        'tong_dien_tich_san' => 'decimal:2',
        'he_so_su_dung_dao_tao' => 'decimal:1',
        'dien_tich_san_dao_tao' => 'decimal:2',
    ];

    public function coSo()
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function phongs()
    {
        return $this->hasMany(Phong::class, 'khu_nha_id');
    }

    public function getTongSoPhongAttribute()
    {
        return $this->phongs()->count();
    }
}

