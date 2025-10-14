<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBaoDuongFieldsToThietBisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('thiet_bis', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->after('ma_thiet_bi');
            $table->date('ngay_mua')->nullable()->after('nam_mua');
            $table->date('ngay_bao_duong_cuoi')->nullable()->after('ngay_mua');
            $table->integer('chu_ky_bao_duong')->default(6)->comment('Chu kỳ bảo dưỡng (tháng)')->after('ngay_bao_duong_cuoi');
            $table->date('ngay_bao_duong_tiep_theo')->nullable()->after('chu_ky_bao_duong');
            $table->text('ghi_chu_bao_duong')->nullable()->after('ngay_bao_duong_tiep_theo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('thiet_bis', function (Blueprint $table) {
            $table->dropColumn([
                'serial_number',
                'ngay_mua',
                'ngay_bao_duong_cuoi',
                'chu_ky_bao_duong',
                'ngay_bao_duong_tiep_theo',
                'ghi_chu_bao_duong',
            ]);
        });
    }
}

