<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDotKiemTraThietBiScreenToScreensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $parentId = DB::table('screens')->where('code', 'thiet-bi-group')->value('id');
        if (!$parentId) {
            return;
        }

        $exists = DB::table('screens')->where('code', 'dot-kiem-tra-thiet-bi')->exists();
        if ($exists) {
            return;
        }

        DB::table('screens')->insert([
            'name' => 'Đợt kiểm tra thiết bị',
            'code' => 'dot-kiem-tra-thiet-bi',
            'route' => '/dot-kiem-tra-thiet-bi',
            'icon' => null,
            'parent_id' => $parentId,
            'order' => 5,
            'is_active' => true,
            'is_menu' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('screens')->where('code', 'dot-kiem-tra-thiet-bi')->delete();
    }
}
