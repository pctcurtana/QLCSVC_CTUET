<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThongKeSnapshot extends Model
{
    protected $table = 'thong_ke_snapshots';

    protected $fillable = [
        'key',
        'value',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'value' => 'array',
        'calculated_at' => 'datetime',
    ];
}
