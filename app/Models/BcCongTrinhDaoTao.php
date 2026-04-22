<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcCongTrinhDaoTao extends Model
{
    use HasFactory;

    protected $table = 'bc_cong_trinh_dao_taos';

    protected $fillable = [
        'dot_bao_cao_id',
        'khu_nha_id',
        'stt',
        'ten_cong_trinh',
        'ky_hieu',
        'tong_dien_tich_san',
        'he_so_dien_tich',
        'dien_tich_san_dao_tao',
        'dia_chi',
        'is_tong',
        'thu_tu',
    ];

    protected $casts = [
        'tong_dien_tich_san' => 'decimal:2',
        'he_so_dien_tich' => 'decimal:2',
        'dien_tich_san_dao_tao' => 'decimal:2',
        'is_tong' => 'boolean',
    ];

    public function dotBaoCao()
    {
        return $this->belongsTo(DotBaoCao::class, 'dot_bao_cao_id');
    }

    public function khuNha()
    {
        return $this->belongsTo(KhuNha::class, 'khu_nha_id');
    }
}
