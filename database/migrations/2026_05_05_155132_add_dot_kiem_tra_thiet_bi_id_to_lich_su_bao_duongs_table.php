<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDotKiemTraThietBiIdToLichSuBaoDuongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lich_su_bao_duongs', function (Blueprint $table) {
            $table->foreignId('dot_kiem_tra_thiet_bi_id')
                ->nullable()
                ->after('dot_bao_cao_id')
                ->constrained('dot_kiem_tra_thiet_bis')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lich_su_bao_duongs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dot_kiem_tra_thiet_bi_id');
        });
    }
}
