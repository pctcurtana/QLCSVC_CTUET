<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcTieuChuanCsvc extends Model
{
    use HasFactory;

    protected $table = 'bc_tieu_chuan_csvcs';

    protected $fillable = [
        'dot_bao_cao_id',
        'ma_chi_so',
        'chi_so_danh_gia',
        'nguong',
        'thuc_te',
        'ket_qua',
        'giai_trinh',
        'thu_tu',
    ];

    public function dotBaoCao()
    {
        return $this->belongsTo(DotBaoCao::class, 'dot_bao_cao_id');
    }
}
