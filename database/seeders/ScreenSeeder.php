<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Screen;
use Illuminate\Support\Facades\DB;

class ScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tắt foreign key checks để có thể truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('user_permissions')->truncate();
        DB::table('screens')->truncate();
        
        // Bật lại foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Dashboard
        Screen::create([
            'name' => 'Dashboard',
            'code' => 'dashboard',
            'route' => '/',
            'icon' => 'DashboardOutlined',
            'parent_id' => null,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 2. Quản lý Cơ sở hạ tầng (standalone)
        Screen::create([
            'name' => 'QL Cơ sở hạ tầng',
            'code' => 'co-so',
            'route' => '/co-so',
            'icon' => 'BankOutlined',
            'parent_id' => null,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 3. Quản lý Khu nhà (standalone)
        Screen::create([
            'name' => 'QL Khu nhà, Chức năng',
            'code' => 'khu-nha',
            'route' => '/khu-nha',
            'icon' => 'HomeOutlined',
            'parent_id' => null,
            'order' => 3,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 4. Quản lý Phòng (standalone)
        Screen::create([
            'name' => 'QL Phòng',
            'code' => 'phong',
            'route' => '/phong',
            'icon' => 'AppstoreOutlined',
            'parent_id' => null,
            'order' => 4,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 5. Thống kê chi tiết (standalone)
        Screen::create([
            'name' => 'Thống kê chi tiết',
            'code' => 'thong-ke',
            'route' => '/thong-ke',
            'icon' => 'AreaChartOutlined',
            'parent_id' => null,
            'order' => 5,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 6. Quản lý Thiết bị
        $thietBiGroup = Screen::create([
            'name' => 'QL Thiết bị',
            'code' => 'thiet-bi-group',
            'route' => null,
            'icon' => 'ToolOutlined',
            'parent_id' => null,
            'order' => 6,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách thiết bị',
            'code' => 'thiet-bi',
            'route' => '/thiet-bi',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Lịch sử bảo dưỡng',
            'code' => 'lich-su-bao-duong',
            'route' => '/lich-su-bao-duong',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Báo cáo sự cố',
            'code' => 'bao-cao-su-co',
            'route' => '/bao-cao-su-co',
            'icon' => 'AlertOutlined',
            'parent_id' => $thietBiGroup->id,
            'order' => 3,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Quản lý Mã QR',
            'code' => 'quan-ly-qr',
            'route' => '/quan-ly-qr',
            'icon' => 'QrcodeOutlined',
            'parent_id' => $thietBiGroup->id,
            'order' => 4,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 7. Xuất báo cáo BGD
        Screen::create([
            'name' => 'Xuất báo cáo BGD',
            'code' => 'xuat-bao-cao',
            'route' => '/xuat-bao-cao',
            'icon' => 'FileExcelOutlined',
            'parent_id' => null,
            'order' => 7,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 8. Quản lý Hệ thống
        $heThongGroup = Screen::create([
            'name' => 'Quản lý Hệ thống',
            'code' => 'he-thong-group',
            'route' => null,
            'icon' => 'SettingOutlined',
            'parent_id' => null,
            'order' => 8,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Quản lý Người dùng',
            'code' => 'nguoi-dung',
            'route' => '/nguoi-dung',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Phân quyền',
            'code' => 'phan-quyen',
            'route' => '/phan-quyen',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        $this->command->info('Đã tạo ' . Screen::count() . ' màn hình');
    }
}
