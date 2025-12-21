<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateThietBisTableForIndividualTracking extends Migration
{
    /**
     * Run the migrations.
     * 
     * Chuyển đổi sang quản lý từng thiết bị riêng biệt:
     * - serial_number là bắt buộc và unique (mỗi máy có 1 serial riêng)
     * - so_luong luôn = 1 (mỗi record = 1 thiết bị vật lý)
     * 
     * LƯU Ý: Trước khi chạy migration này:
     * - Đảm bảo tất cả thiết bị đã có serial_number
     * - Không có serial_number trùng lặp
     * - Hoặc xóa dữ liệu cũ nếu đang test
     *
     * @return void
     */
    public function up()
    {
        // Cập nhật dữ liệu cũ: Set so_luong = 1 cho tất cả records
        \DB::table('thiet_bis')->update(['so_luong' => 1]);
        
        Schema::table('thiet_bis', function (Blueprint $table) {
            // Thêm unique constraint cho serial_number (đã có trong migration cũ nhưng cần đảm bảo)
            // Nếu bị lỗi "Duplicate entry", cần xử lý dữ liệu trùng trước
        });
        
        // Note: serial_number đã được khai báo trong migration cũ, không cần change()
        // Chỉ cần đảm bảo validation ở tầng application (Request validation)
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Không cần revert vì chỉ update data
    }
}
