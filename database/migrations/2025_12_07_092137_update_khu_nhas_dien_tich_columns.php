<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateKhuNhasDienTichColumns extends Migration
{
    /**
     * Run the migrations.
     * 
     * Đổi cấu trúc diện tích khu nhà:
     * - dien_tich_san_xay_dung → tong_dien_tich_san (Tổng DT sàn xây dựng - nhập tay)
     * - dien_tich_su_dung → he_so_su_dung_dao_tao (Hệ số DT sử dụng cho đào tạo - mặc định 0.7)
     * - Thêm: dien_tich_san_dao_tao (= tong_dien_tich_san * he_so_su_dung_dao_tao)
     *
     * @return void
     */
    public function up()
    {
        // Bước 1: Thêm các cột mới
        Schema::table('khu_nhas', function (Blueprint $table) {
            $table->decimal('tong_dien_tich_san', 15, 2)->default(0)->after('so_tang');
            $table->decimal('he_so_su_dung_dao_tao', 5, 2)->default(0.7)->after('tong_dien_tich_san');
            $table->decimal('dien_tich_san_dao_tao', 15, 2)->default(0)->after('he_so_su_dung_dao_tao');
        });

        // Bước 2: Copy dữ liệu từ cột cũ sang cột mới
        \DB::statement('UPDATE khu_nhas SET tong_dien_tich_san = dien_tich_san_xay_dung');
        \DB::statement('UPDATE khu_nhas SET he_so_su_dung_dao_tao = 0.7'); // Mặc định 0.7
        \DB::statement('UPDATE khu_nhas SET dien_tich_san_dao_tao = tong_dien_tich_san * he_so_su_dung_dao_tao');

        // Bước 3: Xóa các cột cũ
        Schema::table('khu_nhas', function (Blueprint $table) {
            $table->dropColumn(['dien_tich_san_xay_dung', 'dien_tich_su_dung']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Bước 1: Thêm lại các cột cũ
        Schema::table('khu_nhas', function (Blueprint $table) {
            $table->decimal('dien_tich_san_xay_dung', 10, 2)->default(0)->after('so_tang');
            $table->decimal('dien_tich_su_dung', 10, 2)->default(0)->after('dien_tich_san_xay_dung');
        });

        // Bước 2: Copy dữ liệu ngược lại
        \DB::statement('UPDATE khu_nhas SET dien_tich_san_xay_dung = tong_dien_tich_san');
        \DB::statement('UPDATE khu_nhas SET dien_tich_su_dung = dien_tich_san_dao_tao');

        // Bước 3: Xóa các cột mới
        Schema::table('khu_nhas', function (Blueprint $table) {
            $table->dropColumn(['tong_dien_tich_san', 'he_so_su_dung_dao_tao', 'dien_tich_san_dao_tao']);
        });
    }
}
