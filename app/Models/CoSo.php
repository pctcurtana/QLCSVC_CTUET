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
        'tong_dien_tich',
        'dien_tich_san_xay_dung',
        'dien_tich_con_lai',
        'mo_ta',
        'trang_thai',
    ];

    protected $casts = [
        'tong_dien_tich' => 'decimal:2',
        'dien_tich_san_xay_dung' => 'decimal:2',
        'dien_tich_con_lai' => 'decimal:2',
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

