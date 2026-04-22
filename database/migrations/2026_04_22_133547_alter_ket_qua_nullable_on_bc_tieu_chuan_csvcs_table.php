<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterKetQuaNullableOnBcTieuChuanCsvcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE bc_tieu_chuan_csvcs MODIFY ket_qua ENUM('dat','khong_dat','chua_danh_gia') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE bc_tieu_chuan_csvcs SET ket_qua = 'chua_danh_gia' WHERE ket_qua IS NULL");
        DB::statement("ALTER TABLE bc_tieu_chuan_csvcs MODIFY ket_qua ENUM('dat','khong_dat','chua_danh_gia') NOT NULL DEFAULT 'chua_danh_gia'");
    }
}
