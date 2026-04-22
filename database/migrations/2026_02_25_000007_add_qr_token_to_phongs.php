<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddQrTokenToPhongs extends Migration
{
    public function up()
    {
        Schema::table('phongs', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('ban_ghi_goc_id');
        });

        // Generate token for existing phong records
        \DB::table('phongs')->whereNull('qr_token')->orderBy('id')->each(function ($phong) {
            \DB::table('phongs')->where('id', $phong->id)->update([
                'qr_token' => Str::uuid(),
            ]);
        });
    }

    public function down()
    {
        Schema::table('phongs', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
}
