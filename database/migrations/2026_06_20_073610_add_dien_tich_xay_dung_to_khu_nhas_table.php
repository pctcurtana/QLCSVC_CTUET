<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDienTichXayDungToKhuNhasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Thêm cột dien_tich_xay_dung (DT xây dựng 1 tầng - nhập tay).
     * tong_dien_tich_san giờ sẽ được tính tự động = dien_tich_xay_dung × so_tang.
     *
     * @return void
     */
    public function up()
    {
        // Bước 1: Thêm cột dien_tich_xay_dung trước cột tong_dien_tich_san
        Schema::table('khu_nhas', function (Blueprint $table) {
            $table->decimal('dien_tich_xay_dung', 15, 2)->default(0)->after('so_tang');
        });

        // Bước 2: Tính giá trị cho dữ liệu hiện có
        // dien_tich_xay_dung = tong_dien_tich_san / so_tang (nếu so_tang > 0)
        \DB::statement('UPDATE khu_nhas SET dien_tich_xay_dung = CASE WHEN so_tang > 0 THEN tong_dien_tich_san / so_tang ELSE tong_dien_tich_san END');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('khu_nhas', function (Blueprint $table) {
            $table->dropColumn('dien_tich_xay_dung');
        });
    }
}
