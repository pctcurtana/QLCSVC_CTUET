<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKhuNhasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('khu_nhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->constrained('co_sos')->onDelete('cascade');
            $table->string('ma_khu_nha')->unique();
            $table->string('ten_khu_nha');
            $table->string('loai_khu_nha'); // phong_hoc, phong_lam_viec, phong_chuc_nang
            $table->integer('so_tang')->default(1);
            $table->decimal('dien_tich_san_xay_dung', 10, 2)->default(0);
            $table->decimal('dien_tich_su_dung', 10, 2)->default(0);
            $table->integer('nam_xay_dung')->nullable();
            $table->text('mo_ta')->nullable();
            $table->string('trang_thai')->default('active');
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
        Schema::dropIfExists('khu_nhas');
    }
}

