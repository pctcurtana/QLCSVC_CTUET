<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddQrTokenToThietBis extends Migration
{
    public function up()
    {
        Schema::table('thiet_bis', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('ban_ghi_goc_id');
        });

        \DB::table('thiet_bis')->whereNull('qr_token')->orderBy('id')->each(function ($tb) {
            \DB::table('thiet_bis')->where('id', $tb->id)->update([
                'qr_token' => Str::uuid(),
            ]);
        });
    }

    public function down()
    {
        Schema::table('thiet_bis', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
}
