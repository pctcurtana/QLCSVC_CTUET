<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcLoaiPhong extends Model
{
    use HasFactory;

    protected $table = 'bc_loai_phongs';

    protected $fillable = [
        'dot_bao_cao_id',
        'stt',
        'loai_phong',
        'so_luong',
        'dien_tich',
        'is_tong',
        'thu_tu',
    ];

    protected $casts = [
        'so_luong' => 'integer',
        'dien_tich' => 'decimal:2',
        'is_tong' => 'boolean',
    ];

    public function dotBaoCao()
    {
        return $this->belongsTo(DotBaoCao::class, 'dot_bao_cao_id');
    }
}
