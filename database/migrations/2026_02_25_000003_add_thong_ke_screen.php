<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddThongKeScreen extends Migration
{
    public function up()
    {
        // Chèn vào order 5.5 (giữa thiet-bi-group=5 và he-thong-group=6)
        // Đẩy he-thong-group lên order 7 để nhường chỗ
        DB::table('screens')->where('code', 'he-thong-group')->update(['order' => 7]);

        DB::table('screens')->insert([
            'name'       => 'Thống kê chi tiết',
            'code'       => 'thong-ke',
            'route'      => '/thong-ke',
            'icon'       => 'AreaChartOutlined',
            'parent_id'  => null,
            'order'      => 6,
            'is_active'  => true,
            'is_menu'    => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('screens')->where('code', 'thong-ke')->delete();
        DB::table('screens')->where('code', 'he-thong-group')->update(['order' => 6]);
    }
}
