<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->boolean('can_regenerate_qr')->default(false)->after('can_delete');
            $table->boolean('can_import')->default(false)->after('can_regenerate_qr');
            $table->boolean('can_export')->default(false)->after('can_import');
        });
    }

    public function down(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropColumn(['can_regenerate_qr', 'can_import', 'can_export']);
        });
    }
};
