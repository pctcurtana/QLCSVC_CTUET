<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDangSuaChuaStatusToBaoCaoSuCos extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE bao_cao_su_cos
            MODIFY COLUMN trang_thai ENUM('yeu_cau_sua_chua', 'dang_sua_chua', 'hoan_thanh_sua_chua')
            NOT NULL DEFAULT 'yeu_cau_sua_chua'");
    }

    public function down()
    {
        DB::statement("UPDATE bao_cao_su_cos SET trang_thai = 'yeu_cau_sua_chua' WHERE trang_thai = 'dang_sua_chua'");

        DB::statement("ALTER TABLE bao_cao_su_cos
            MODIFY COLUMN trang_thai ENUM('yeu_cau_sua_chua', 'hoan_thanh_sua_chua')
            NOT NULL DEFAULT 'yeu_cau_sua_chua'");
    }
}
