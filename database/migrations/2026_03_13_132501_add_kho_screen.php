<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddKhoScreen extends Migration
{
    public function up()
    {
        // Lấy parent_id của thiet-bi-group
        $thietBiGroupId = DB::table('screens')->where('code', 'thiet-bi-group')->value('id');

        if (!$thietBiGroupId) {
            return;
        }

        // Chỉ thêm nếu chưa tồn tại
        if (DB::table('screens')->where('code', 'kho')->exists()) {
            return;
        }

        // Đặt kho ở order 3 trong group (sau thiet-bi=1, lich-su-bao-duong=2)
        DB::table('screens')->insert([
            'name'       => 'Kho thiết bị',
            'code'       => 'kho',
            'route'      => '/kho',
            'icon'       => 'InboxOutlined',
            'parent_id'  => $thietBiGroupId,
            'order'      => 3,
            'is_active'  => true,
            'is_menu'    => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('screens')->where('code', 'kho')->delete();
    }
}
