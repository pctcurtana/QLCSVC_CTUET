<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPhanUngNhanhScreens extends Migration
{
    public function up()
    {
        $thietBiGroup = DB::table('screens')->where('code', 'thiet-bi-group')->first();

        if ($thietBiGroup) {
            DB::table('screens')->insert([
                [
                    'name'      => 'Báo cáo sự cố',
                    'code'      => 'bao-cao-su-co',
                    'route'     => '/bao-cao-su-co',
                    'icon'      => 'AlertOutlined',
                    'parent_id' => $thietBiGroup->id,
                    'order'     => 3,
                    'is_active' => true,
                    'is_menu'   => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name'      => 'Quản lý Mã QR',
                    'code'      => 'quan-ly-qr',
                    'route'     => '/quan-ly-qr',
                    'icon'      => 'QrcodeOutlined',
                    'parent_id' => $thietBiGroup->id,
                    'order'     => 4,
                    'is_active' => true,
                    'is_menu'   => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down()
    {
        DB::table('screens')->whereIn('code', ['bao-cao-su-co', 'quan-ly-qr'])->delete();
    }
}
