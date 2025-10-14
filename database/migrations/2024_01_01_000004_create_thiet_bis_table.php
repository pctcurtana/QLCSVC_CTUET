<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThietBisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thiet_bis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phong_id')->nullable()->constrained('phongs')->onDelete('set null');
            $table->string('ma_thiet_bi')->unique();
            $table->string('ten_thiet_bi');
            $table->string('loai_thiet_bi'); // van_phong, day_hoc, thi_nghiem, thuc_hanh
            $table->string('hang_san_xuat')->nullable();
            $table->string('model')->nullable();
            $table->integer('nam_mua')->nullable();
            $table->decimal('gia_tri', 15, 2)->default(0);
            $table->integer('so_luong')->default(1);
            $table->string('don_vi_tinh')->default('cái');
            $table->text('thong_so_ky_thuat')->nullable();
            $table->text('mo_ta')->nullable();
            $table->string('trang_thai')->default('tot'); // tot, can_sua_chua, hu_hong
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
        Schema::dropIfExists('thiet_bis');
    }
}

