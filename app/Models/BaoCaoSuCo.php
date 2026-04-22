<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaoCaoSuCo extends Model
{
    use HasFactory;

    protected $table = 'bao_cao_su_cos';

    protected $fillable = [
        'phong_id',
        'thiet_bi_id',
        'ten_nguoi_bao',
        'so_dien_thoai',
        'mo_ta_su_co',
        'muc_do',
        'trang_thai',
        'ip_address',
        'nguoi_hoan_thanh',
        'ngay_hoan_thanh',
    ];

    protected $casts = [
        'ngay_hoan_thanh' => 'datetime',
    ];

    public function phong()
    {
        return $this->belongsTo(Phong::class, 'phong_id');
    }

    public function thietBi()
    {
        return $this->belongsTo(ThietBi::class, 'thiet_bi_id');
    }
}
