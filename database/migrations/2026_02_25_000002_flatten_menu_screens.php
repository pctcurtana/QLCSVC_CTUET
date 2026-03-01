<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Chuyển co-so, khu-nha, phong từ dạng group+child sang standalone menu item.
 * Xóa các group screen thừa (co-so-group, khu-nha-group, phong-group).
 *
 * QUAN TRỌNG: Phải UPDATE children ra khỏi parent TRƯỚC khi DELETE groups,
 * vì bảng screens có onDelete('cascade') - xóa group sẽ kéo theo xóa children.
 */
class FlattenMenuScreens extends Migration
{
    private $standaloneItems = [
        'co-so'   => ['name' => 'QL Cơ sở hạ tầng',       'route' => '/co-so',   'icon' => 'BankOutlined',    'order' => 2],
        'khu-nha' => ['name' => 'QL Khu nhà, Chức năng',   'route' => '/khu-nha', 'icon' => 'HomeOutlined',    'order' => 3],
        'phong'   => ['name' => 'QL Phòng',                 'route' => '/phong',   'icon' => 'AppstoreOutlined','order' => 4],
    ];

    public function up()
    {
        // Bước 1: Đưa children lên standalone TRƯỚC (tránh cascade delete)
        foreach ($this->standaloneItems as $code => $attrs) {
            $exists = DB::table('screens')->where('code', $code)->exists();

            if ($exists) {
                // Screen đang là child → đưa lên standalone
                DB::table('screens')->where('code', $code)->update([
                    'name'      => $attrs['name'],
                    'parent_id' => null,
                    'icon'      => $attrs['icon'],
                    'order'     => $attrs['order'],
                ]);
            } else {
                // Screen bị mất (vd: cascade delete trước đó) → tạo lại
                DB::table('screens')->insert([
                    'name'       => $attrs['name'],
                    'code'       => $code,
                    'route'      => $attrs['route'],
                    'icon'       => $attrs['icon'],
                    'parent_id'  => null,
                    'order'      => $attrs['order'],
                    'is_active'  => true,
                    'is_menu'    => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Bước 2: Bây giờ mới xóa group screens (children đã standalone, không còn cascade)
        $groupCodes = ['co-so-group', 'khu-nha-group', 'phong-group'];
        $groupIds = DB::table('screens')
            ->whereIn('code', $groupCodes)
            ->pluck('id')
            ->toArray();

        if (!empty($groupIds)) {
            // Xóa user_permissions liên quan đến group screens
            DB::table('user_permissions')
                ->whereIn('screen_id', $groupIds)
                ->delete();

            DB::table('screens')
                ->whereIn('id', $groupIds)
                ->delete();
        }
    }

    public function down()
    {
        // Tạo lại group screens
        $coSoGroupId = DB::table('screens')->insertGetId([
            'name'       => 'QL Cơ sở hạ tầng',
            'code'       => 'co-so-group',
            'route'      => null,
            'icon'       => 'BankOutlined',
            'parent_id'  => null,
            'order'      => 2,
            'is_active'  => true,
            'is_menu'    => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $khuNhaGroupId = DB::table('screens')->insertGetId([
            'name'       => 'QL Khu nhà, Chức năng',
            'code'       => 'khu-nha-group',
            'route'      => null,
            'icon'       => 'HomeOutlined',
            'parent_id'  => null,
            'order'      => 3,
            'is_active'  => true,
            'is_menu'    => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $phongGroupId = DB::table('screens')->insertGetId([
            'name'       => 'QL Phòng',
            'code'       => 'phong-group',
            'route'      => null,
            'icon'       => 'AppstoreOutlined',
            'parent_id'  => null,
            'order'      => 4,
            'is_active'  => true,
            'is_menu'    => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Đưa standalone screens về lại làm children
        DB::table('screens')->where('code', 'co-so')->update([
            'name'      => 'Danh sách cơ sở',
            'parent_id' => $coSoGroupId,
            'icon'      => null,
            'order'     => 1,
        ]);

        DB::table('screens')->where('code', 'khu-nha')->update([
            'name'      => 'Danh sách khu nhà',
            'parent_id' => $khuNhaGroupId,
            'icon'      => null,
            'order'     => 1,
        ]);

        DB::table('screens')->where('code', 'phong')->update([
            'name'      => 'Danh sách phòng',
            'parent_id' => $phongGroupId,
            'icon'      => null,
            'order'     => 1,
        ]);
    }
}
