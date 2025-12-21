<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCoSosDienTichColumns extends Migration
{
    /**
     * Run the migrations.
     * 
     * Đổi cấu trúc diện tích:
     * - tong_dien_tich → dien_tich_dat (Diện tích đất - nhập tay)
     * - dien_tich_san_xay_dung → vi_tri_khuon_vien (Vị trí khuôn viên - mặc định 2.5 theo BGD)
     * - dien_tich_con_lai → dien_tich_quy_doi (= dien_tich_dat * vi_tri_khuon_vien)
     *
     * @return void
     */
    public function up()
    {
        // Bước 1: Thêm các cột mới
        Schema::table('co_sos', function (Blueprint $table) {
            $table->decimal('dien_tich_dat', 10, 2)->default(0)->after('dia_chi');
            $table->decimal('vi_tri_khuon_vien', 5, 2)->default(2.5)->after('dien_tich_dat');
            $table->decimal('dien_tich_quy_doi', 15, 2)->default(0)->after('vi_tri_khuon_vien');
        });

        // Bước 2: Copy dữ liệu từ cột cũ sang cột mới
        \DB::statement('UPDATE co_sos SET dien_tich_dat = tong_dien_tich');
        \DB::statement('UPDATE co_sos SET vi_tri_khuon_vien = 2.5'); // Mặc định 2.5 theo BGD
        \DB::statement('UPDATE co_sos SET dien_tich_quy_doi = dien_tich_dat * vi_tri_khuon_vien');

        // Bước 3: Xóa các cột cũ
        Schema::table('co_sos', function (Blueprint $table) {
            $table->dropColumn(['tong_dien_tich', 'dien_tich_san_xay_dung', 'dien_tich_con_lai']);
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
        Schema::table('co_sos', function (Blueprint $table) {
            $table->decimal('tong_dien_tich', 10, 2)->default(0)->after('dia_chi');
            $table->decimal('dien_tich_san_xay_dung', 10, 2)->default(0)->after('tong_dien_tich');
            $table->decimal('dien_tich_con_lai', 10, 2)->default(0)->after('dien_tich_san_xay_dung');
        });

        // Bước 2: Copy dữ liệu ngược lại
        \DB::statement('UPDATE co_sos SET tong_dien_tich = dien_tich_dat');
        \DB::statement('UPDATE co_sos SET dien_tich_san_xay_dung = 0');
        \DB::statement('UPDATE co_sos SET dien_tich_con_lai = tong_dien_tich');

        // Bước 3: Xóa các cột mới
        Schema::table('co_sos', function (Blueprint $table) {
            $table->dropColumn(['dien_tich_dat', 'vi_tri_khuon_vien', 'dien_tich_quy_doi']);
        });
    }
}
