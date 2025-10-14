<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLichSuBaoDuongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lich_su_bao_duongs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thiet_bi_id')->constrained('thiet_bis')->onDelete('cascade');
            $table->date('ngay_bao_duong');
            $table->string('loai_bao_duong'); // dinh_ky, sua_chua, thay_the
            $table->text('noi_dung');
            $table->decimal('chi_phi', 15, 2)->default(0);
            $table->string('nguoi_thuc_hien')->nullable();
            $table->string('don_vi_thuc_hien')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->string('trang_thai')->default('hoan_thanh'); // hoan_thanh, dang_thuc_hien, chua_thuc_hien
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lich_su_bao_duongs');
    }
}

