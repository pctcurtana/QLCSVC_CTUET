<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcKhuonVien extends Model
{
    use HasFactory;

    protected $table = 'bc_khuon_viens';

    protected $fillable = [
        'dot_bao_cao_id',
        'co_so_id',
        'ten_khuon_vien',
        'ky_hieu',
        'hinh_thuc_su_dung',
        'dien_tich_dat',
        'vi_tri_khuon_vien',
        'dien_tich_quy_doi',
        'dia_chi',
        'is_tong',
        'thu_tu',
    ];

    protected $casts = [
        'dien_tich_dat' => 'decimal:2',
        'vi_tri_khuon_vien' => 'decimal:2',
        'dien_tich_quy_doi' => 'decimal:2',
        'is_tong' => 'boolean',
    ];

    public function dotBaoCao()
    {
        return $this->belongsTo(DotBaoCao::class, 'dot_bao_cao_id');
    }

    public function coSo()
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }
}
