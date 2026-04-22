<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('screens')
            ->where('code', 'kho')
            ->update([
                'name' => 'Lịch sử thiết bị',
                'icon' => 'HistoryOutlined',
            ]);
    }

    public function down(): void
    {
        DB::table('screens')
            ->where('code', 'kho')
            ->update([
                'name' => 'Kho thiết bị',
                'icon' => 'InboxOutlined',
            ]);
    }
};
