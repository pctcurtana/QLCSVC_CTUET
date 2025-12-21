<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('screen_id');
            $table->boolean('can_view')->default(false);     // Quyền xem
            $table->boolean('can_create')->default(false);   // Quyền thêm
            $table->boolean('can_edit')->default(false);     // Quyền sửa
            $table->boolean('can_delete')->default(false);   // Quyền xóa
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('screen_id')
                  ->references('id')
                  ->on('screens')
                  ->onDelete('cascade');

            // Mỗi user chỉ có 1 bản ghi permission cho mỗi screen
            $table->unique(['user_id', 'screen_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_permissions');
    }
}


