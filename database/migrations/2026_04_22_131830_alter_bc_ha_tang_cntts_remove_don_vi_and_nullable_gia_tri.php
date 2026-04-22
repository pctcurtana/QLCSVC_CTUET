<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterBcHaTangCnttsRemoveDonViAndNullableGiaTri extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE bc_ha_tang_cntts MODIFY gia_tri DECIMAL(15,2) NULL');
        DB::statement('ALTER TABLE bc_ha_tang_cntts DROP COLUMN don_vi');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE bc_ha_tang_cntts ADD COLUMN don_vi VARCHAR(50) NULL AFTER gia_tri");
        DB::statement('ALTER TABLE bc_ha_tang_cntts MODIFY gia_tri DECIMAL(15,2) NOT NULL DEFAULT 0');
    }
}
