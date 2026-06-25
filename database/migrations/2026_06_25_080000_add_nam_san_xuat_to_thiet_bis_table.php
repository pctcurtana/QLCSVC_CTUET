<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNamSanXuatToThietBisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('thiet_bis', function (Blueprint $table) {
            $table->integer('nam_san_xuat')->nullable()->after('model');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('thiet_bis', function (Blueprint $table) {
            $table->dropColumn('nam_san_xuat');
        });
    }
}
