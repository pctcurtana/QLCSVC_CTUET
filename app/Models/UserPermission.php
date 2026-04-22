<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'screen_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'can_regenerate_qr',
        'can_import',
        'can_export',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'can_regenerate_qr' => 'boolean',
        'can_import' => 'boolean',
        'can_export' => 'boolean',
    ];

    /**
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Screen
     */
    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    /**
     * Kiểm tra có bất kỳ quyền nào không
     */
    public function hasAnyPermission()
    {
        return $this->can_view || $this->can_create || $this->can_edit || $this->can_delete
            || $this->can_regenerate_qr || $this->can_import || $this->can_export;
    }
}


