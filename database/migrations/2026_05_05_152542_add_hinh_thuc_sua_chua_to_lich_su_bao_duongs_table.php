<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHinhThucSuaChuaToLichSuBaoDuongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lich_su_bao_duongs', function (Blueprint $table) {
            $table->string('hinh_thuc_sua_chua', 30)
                ->default('dot_xuat')
                ->after('loai_bao_duong');
            $table->foreignId('dot_bao_cao_id')
                ->nullable()
                ->after('hinh_thuc_sua_chua')
                ->constrained('dot_bao_caos')
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
            $table->dropConstrainedForeignId('dot_bao_cao_id');
            $table->dropColumn('hinh_thuc_sua_chua');
        });
    }
}
