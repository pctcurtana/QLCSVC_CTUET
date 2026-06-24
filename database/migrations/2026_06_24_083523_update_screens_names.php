<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateScreensNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('screens')->where('code', 'co-so')->update(['name' => 'Cơ sở hạ tầng']);
        \Illuminate\Support\Facades\DB::table('screens')->where('code', 'khu-nha')->update(['name' => 'Toà nhà, Chức năng']);
        \Illuminate\Support\Facades\DB::table('screens')->where('code', 'phong')->update(['name' => 'Phòng']);
        \Illuminate\Support\Facades\DB::table('screens')->where('code', 'thiet-bi-group')->update(['name' => 'Thiết bị']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::table('screens')->where('code', 'co-so')->update(['name' => 'QL Cơ sở hạ tầng']);
        \Illuminate\Support\Facades\DB::table('screens')->where('code', 'khu-nha')->update(['name' => 'QL Khu nhà, Chức năng']);
        \Illuminate\Support\Facades\DB::table('screens')->where('code', 'phong')->update(['name' => 'QL Phòng']);
        \Illuminate\Support\Facades\DB::table('screens')->where('code', 'thiet-bi-group')->update(['name' => 'QL Thiết bị']);
    }
}
