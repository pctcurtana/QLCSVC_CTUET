<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'module',
        'file_path',
        'original_filename',
        'status',
        'total',
        'created',
        'updated',
        'errors',
        'error_details',
        'execution_time',
        'error_message',
    ];

    protected $casts = [
        'total' => 'integer',
        'created' => 'integer',
        'updated' => 'integer',
        'errors' => 'integer',
        'execution_time' => 'float',
        'error_details' => 'array',
    ];

    /**
     * Relationship với User thực hiện import.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tự động đánh dấu failed nếu tiến trình import bị kẹt quá lâu (hơn 1 giờ - 3600s) do worker ngắt/chết.
     */
    public static function cleanupStaleImports()
    {
        self::whereIn('status', ['pending', 'processing'])
            ->where('updated_at', '<=', now()->subSeconds(3660))
            ->update([
                'status'        => 'failed',
                'error_message' => 'Tiến trình import bị hủy do worker bị gián đoạn hoặc quá thời gian timeout (3600s).',
            ]);
    }
}
