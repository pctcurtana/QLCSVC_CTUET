<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaoCaoSuCosTable extends Migration
{
    public function up()
    {
        Schema::create('bao_cao_su_cos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('phong_id');
            $table->unsignedBigInteger('thiet_bi_id')->nullable();
            $table->string('ten_nguoi_bao');
            $table->string('so_dien_thoai', 20)->nullable();
            $table->text('mo_ta_su_co');
            $table->enum('muc_do', ['thap', 'trung_binh', 'cao', 'khan_cap'])->default('trung_binh');
            $table->enum('trang_thai', ['moi', 'dang_xu_ly', 'da_xu_ly'])->default('moi');
            $table->string('ip_address', 45)->nullable();
            $table->text('ghi_chu_xu_ly')->nullable();
            $table->string('nguoi_xu_ly')->nullable();
            $table->timestamp('ngay_xu_ly')->nullable();
            $table->timestamps();

            $table->foreign('phong_id')->references('id')->on('phongs')->onDelete('cascade');
            $table->foreign('thiet_bi_id')->references('id')->on('thiet_bis')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bao_cao_su_cos');
    }
}
