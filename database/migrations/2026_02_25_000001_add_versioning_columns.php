<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Thêm cơ chế quản lý phiên bản dữ liệu (SCD Type 2)
 *
 * Mục đích: Lưu trữ lịch sử thay đổi dữ liệu, phục vụ xuất báo cáo theo mốc thời gian.
 *
 * Các cột thêm mới vào 4 bảng: co_sos, khu_nhas, phongs, thiet_bis:
 *   - trang_thai_du_lieu: 'hien_hanh' (đang hiệu lực) hoặc 'lich_su' (đã lưu trữ)
 *   - hieu_luc_tu:        Ngày bản ghi bắt đầu có hiệu lực
 *   - hieu_luc_den:       Ngày bản ghi hết hiệu lực (null = đang hiệu lực)
 *   - phien_ban:          Số thứ tự phiên bản (1, 2, 3...)
 *   - ban_ghi_goc_id:     ID bản ghi gốc - nhóm tất cả phiên bản của cùng 1 thực thể
 *
 * Đồng thời xóa unique constraints từ ma_* ở DB level
 * (unique được kiểm tra ở tầng ứng dụng, chỉ trong phạm vi 'hien_hanh').
 */
class AddVersioningColumns extends Migration
{
    private $tables = ['co_sos', 'khu_nhas', 'phongs', 'thiet_bis'];

    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->string('trang_thai_du_lieu', 20)->default('hien_hanh')->after('trang_thai');
                $t->timestamp('hieu_luc_tu')->nullable()->after('trang_thai_du_lieu');
                $t->timestamp('hieu_luc_den')->nullable()->after('hieu_luc_tu');
                $t->tinyInteger('phien_ban')->unsigned()->default(1)->after('hieu_luc_den');
                $t->unsignedBigInteger('ban_ghi_goc_id')->nullable()->after('phien_ban');

                // Index để query nhanh
                $t->index('trang_thai_du_lieu', "idx_{$table}_ttdl");
                $t->index(['ban_ghi_goc_id', 'trang_thai_du_lieu'], "idx_{$table}_goc_ttdl");
            });
        }

        // Gán hieu_luc_tu = created_at cho tất cả bản ghi hiện có
        foreach ($this->tables as $table) {
            \DB::statement("UPDATE {$table} SET hieu_luc_tu = created_at WHERE hieu_luc_tu IS NULL");
        }

        // Xóa unique constraints từ ma_* (sẽ kiểm tra ở tầng ứng dụng)
        Schema::table('co_sos', function (Blueprint $table) {
            $table->dropUnique(['ma_co_so']);
        });
        Schema::table('khu_nhas', function (Blueprint $table) {
            $table->dropUnique(['ma_khu_nha']);
        });
        Schema::table('phongs', function (Blueprint $table) {
            $table->dropUnique(['ma_phong']);
        });
        Schema::table('thiet_bis', function (Blueprint $table) {
            $table->dropUnique(['ma_thiet_bi']);
        });

        // Thêm unique partial index (chỉ unique trong phạm vi hien_hanh) - dùng raw SQL
        // MySQL không hỗ trợ partial index, nên kiểm tra ở tầng ứng dụng là đủ
    }

    public function down()
    {
        // Xóa các unique constraints đã drop, thêm lại
        // Trước tiên cần xóa dữ liệu lịch sử (phiên bản cũ) để không bị duplicate
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropIndex("idx_{$table}_ttdl");
                $t->dropIndex("idx_{$table}_goc_ttdl");
                $t->dropColumn(['trang_thai_du_lieu', 'hieu_luc_tu', 'hieu_luc_den', 'phien_ban', 'ban_ghi_goc_id']);
            });
        }

        // Thêm lại unique constraints
        Schema::table('co_sos', function (Blueprint $table) {
            $table->unique('ma_co_so');
        });
        Schema::table('khu_nhas', function (Blueprint $table) {
            $table->unique('ma_khu_nha');
        });
        Schema::table('phongs', function (Blueprint $table) {
            $table->unique('ma_phong');
        });
        Schema::table('thiet_bis', function (Blueprint $table) {
            $table->unique('ma_thiet_bi');
        });
    }
}
