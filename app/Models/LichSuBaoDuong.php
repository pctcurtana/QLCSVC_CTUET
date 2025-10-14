<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichSuBaoDuong extends Model
{
    use HasFactory;

    protected $table = 'lich_su_bao_duongs';

    protected $fillable = [
        'thiet_bi_id',
        'ngay_bao_duong',
        'loai_bao_duong',
        'noi_dung',
        'chi_phi',
        'nguoi_thuc_hien',
        'don_vi_thuc_hien',
        'ghi_chu',
        'trang_thai',
    ];

    protected $casts = [
        'ngay_bao_duong' => 'date',
        'chi_phi' => 'decimal:2',
    ];

    public function thietBi()
    {
        return $this->belongsTo(ThietBi::class, 'thiet_bi_id');
    }
}

