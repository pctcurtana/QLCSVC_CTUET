<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScreensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Tên màn hình
            $table->string('code')->unique();                // Mã màn hình (dùng để check quyền)
            $table->string('route')->nullable();             // Route path (VD: /co-so, /khu-nha)
            $table->string('icon')->nullable();              // Icon class name
            $table->unsignedBigInteger('parent_id')->nullable(); // Parent ID cho đệ quy
            $table->integer('order')->default(0);            // Thứ tự hiển thị
            $table->boolean('is_active')->default(true);     // Trạng thái hoạt động
            $table->boolean('is_menu')->default(true);       // Có hiển thị trên menu không
            $table->timestamps();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('screens')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('screens');
    }
}


