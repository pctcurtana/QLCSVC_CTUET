<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoSo extends Model
{
    use HasFactory;

    protected $table = 'co_sos';

    protected $fillable = [
        'ma_co_so',
        'ten_co_so',
        'dia_chi',
        'dien_tich_dat',        // Diện tích đất (m²) - nhập tay
        'vi_tri_khuon_vien',    // Vị trí khuôn viên (hệ số) - mặc định 2.5 theo BGD
        'dien_tich_quy_doi',    // Diện tích quy đổi = dien_tich_dat * vi_tri_khuon_vien
        'mo_ta',
        'trang_thai',
    ];

    protected $casts = [
        'dien_tich_dat' => 'decimal:2',
        'vi_tri_khuon_vien' => 'decimal:1',
        'dien_tich_quy_doi' => 'decimal:2',
    ];

    public function khuNhas()
    {
        return $this->hasMany(KhuNha::class, 'co_so_id');
    }

    public function getTongSoKhuNhaAttribute()
    {
        return $this->khuNhas()->count();
    }

    public function getTongSoPhongAttribute()
    {
        return Phong::whereHas('khuNha', function($query) {
            $query->where('co_so_id', $this->id);
        })->count();
    }
}

