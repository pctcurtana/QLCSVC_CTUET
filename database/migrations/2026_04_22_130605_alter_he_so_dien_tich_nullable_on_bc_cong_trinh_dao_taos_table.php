<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterHeSoDienTichNullableOnBcCongTrinhDaoTaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE bc_cong_trinh_dao_taos MODIFY he_so_dien_tich DECIMAL(5,2) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE bc_cong_trinh_dao_taos MODIFY he_so_dien_tich DECIMAL(5,2) NOT NULL DEFAULT 0.70');
    }
}
