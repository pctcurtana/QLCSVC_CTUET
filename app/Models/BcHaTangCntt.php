<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcHaTangCntt extends Model
{
    use HasFactory;

    protected $table = 'bc_ha_tang_cntts';

    protected $fillable = [
        'dot_bao_cao_id',
        'stt',
        'chi_so_thong_ke',
        'gia_tri',
        'ghi_chu',
        'thu_tu',
    ];

    protected $casts = [
        'gia_tri' => 'decimal:2',
    ];

    public function dotBaoCao()
    {
        return $this->belongsTo(DotBaoCao::class, 'dot_bao_cao_id');
    }
}
