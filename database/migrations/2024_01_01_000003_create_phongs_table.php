<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phongs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khu_nha_id')->constrained('khu_nhas')->onDelete('cascade');
            $table->string('ma_phong')->unique();
            $table->string('ten_phong');
            $table->string('loai_phong'); // phong_hoc, phong_thi_nghiem, phong_thuc_hanh, phong_lam_viec, phong_chuc_nang
            $table->integer('tang')->default(1);
            $table->decimal('dien_tich', 10, 2)->default(0);
            $table->integer('suc_chua')->default(0); // số người
            $table->text('trang_thiet_bi')->nullable();
            $table->text('mo_ta')->nullable();
            $table->string('trang_thai')->default('active'); // active, maintenance, inactive
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
        Schema::dropIfExists('phongs');
    }
}

