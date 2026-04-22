<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SimplifyBaoCaoSuCoTrangThai extends Migration
{
    public function up()
    {
        // Step 1: Expand enum to all values so UPDATEs don't fail in strict mode
        DB::statement("ALTER TABLE bao_cao_su_cos
            MODIFY COLUMN trang_thai ENUM('moi','dang_xu_ly','da_xu_ly','yeu_cau_sua_chua','hoan_thanh_sua_chua')
            NOT NULL DEFAULT 'moi'");

        // Step 2: Migrate existing rows to new values
        DB::statement("UPDATE bao_cao_su_cos SET trang_thai = 'yeu_cau_sua_chua'    WHERE trang_thai IN ('moi','dang_xu_ly')");
        DB::statement("UPDATE bao_cao_su_cos SET trang_thai = 'hoan_thanh_sua_chua' WHERE trang_thai = 'da_xu_ly'");

        // Step 3: Lock to only the 2 new values
        DB::statement("ALTER TABLE bao_cao_su_cos
            MODIFY COLUMN trang_thai ENUM('yeu_cau_sua_chua','hoan_thanh_sua_chua')
            NOT NULL DEFAULT 'yeu_cau_sua_chua'");

        // Step 4: Drop old columns if they exist
        Schema::table('bao_cao_su_cos', function (Blueprint $table) {
            $toDrop = [];
            foreach (['ghi_chu_xu_ly', 'nguoi_xu_ly', 'ngay_xu_ly'] as $col) {
                if (Schema::hasColumn('bao_cao_su_cos', $col)) {
                    $toDrop[] = $col;
                }
            }
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });

        // Step 5: Add new tracking columns if they don't exist
        Schema::table('bao_cao_su_cos', function (Blueprint $table) {
            if (!Schema::hasColumn('bao_cao_su_cos', 'nguoi_hoan_thanh')) {
                $table->string('nguoi_hoan_thanh')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('bao_cao_su_cos', 'ngay_hoan_thanh')) {
                $table->timestamp('ngay_hoan_thanh')->nullable()->after('nguoi_hoan_thanh');
            }
        });
    }

    public function down()
    {
        DB::statement("ALTER TABLE bao_cao_su_cos
            MODIFY COLUMN trang_thai ENUM('moi','dang_xu_ly','da_xu_ly')
            NOT NULL DEFAULT 'moi'");
    }
}
