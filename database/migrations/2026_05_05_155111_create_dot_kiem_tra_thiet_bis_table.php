<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDotKiemTraThietBisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dot_kiem_tra_thiet_bis', function (Blueprint $table) {
            $table->id();
            $table->string('ten_dot');
            $table->string('ma_dot')->nullable();
            $table->date('ngay_bat_dau')->nullable();
            $table->date('ngay_ket_thuc')->nullable();
            $table->text('mo_ta')->nullable();
            $table->boolean('is_active')->default(false);
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('users')->nullOnDelete();
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
        Schema::dropIfExists('dot_kiem_tra_thiet_bis');
    }
}
