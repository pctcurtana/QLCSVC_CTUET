<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoSosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('co_sos', function (Blueprint $table) {
            $table->id();
            $table->string('ma_co_so')->unique();
            $table->string('ten_co_so');
            $table->string('dia_chi');
            $table->decimal('tong_dien_tich', 10, 2)->default(0); // m2
            $table->decimal('dien_tich_san_xay_dung', 10, 2)->default(0);
            $table->decimal('dien_tich_con_lai', 10, 2)->default(0);
            $table->text('mo_ta')->nullable();
            $table->string('trang_thai')->default('active'); // active, inactive
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
        Schema::dropIfExists('co_sos');
    }
}

