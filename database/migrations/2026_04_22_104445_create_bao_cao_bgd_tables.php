<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng chính: Đợt báo cáo
        Schema::create('dot_bao_caos', function (Blueprint $table) {
            $table->id();
            $table->string('ten_dot', 255);
            $table->string('nam_hoc', 20)->nullable();
            $table->text('mo_ta')->nullable();
            $table->date('ngay_tong_hop')->nullable();
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('trang_thai', ['draft', 'completed'])->default('draft');
            $table->timestamps();
        });

        // Báo cáo 1: Loại phòng phục vụ tuyển sinh
        Schema::create('bc_loai_phongs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dot_bao_cao_id')->constrained('dot_bao_caos')->cascadeOnDelete();
            $table->string('stt', 10);
            $table->string('loai_phong', 500);
            $table->integer('so_luong')->default(0);
            $table->decimal('dien_tich', 15, 2)->default(0);
            $table->boolean('is_tong')->default(false);
            $table->integer('thu_tu')->default(0);
            $table->timestamps();
        });

        // Báo cáo 2: Tiêu chuẩn 3 - Cơ sở vật chất
        Schema::create('bc_tieu_chuan_csvcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dot_bao_cao_id')->constrained('dot_bao_caos')->cascadeOnDelete();
            $table->string('ma_chi_so', 20);
            $table->string('chi_so_danh_gia', 500);
            $table->string('nguong', 50)->nullable();
            $table->string('thuc_te', 50)->nullable();
            $table->enum('ket_qua', ['dat', 'khong_dat', 'chua_danh_gia'])->default('chua_danh_gia');
            $table->text('giai_trinh')->nullable();
            $table->integer('thu_tu')->default(0);
            $table->timestamps();
        });

        // Báo cáo 3A: Khuôn viên trụ sở chính và các phân hiệu
        Schema::create('bc_khuon_viens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dot_bao_cao_id')->constrained('dot_bao_caos')->cascadeOnDelete();
            $table->foreignId('co_so_id')->nullable()->constrained('co_sos')->nullOnDelete();
            $table->string('ten_khuon_vien', 255);
            $table->string('ky_hieu', 100)->nullable();
            $table->string('hinh_thuc_su_dung', 100)->nullable();
            $table->decimal('dien_tich_dat', 15, 2)->default(0);
            $table->decimal('vi_tri_khuon_vien', 10, 2)->nullable();
            $table->decimal('dien_tich_quy_doi', 15, 2)->default(0);
            $table->string('dia_chi', 500)->nullable();
            $table->boolean('is_tong')->default(false);
            $table->integer('thu_tu')->default(0);
            $table->timestamps();
        });

        // Báo cáo 3B: Công trình phục vụ đào tạo
        Schema::create('bc_cong_trinh_dao_taos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dot_bao_cao_id')->constrained('dot_bao_caos')->cascadeOnDelete();
            $table->foreignId('khu_nha_id')->nullable()->constrained('khu_nhas')->nullOnDelete();
            $table->integer('stt')->default(0);
            $table->string('ten_cong_trinh', 255);
            $table->string('ky_hieu', 100)->nullable();
            $table->decimal('tong_dien_tich_san', 15, 2)->default(0);
            $table->decimal('he_so_dien_tich', 5, 2)->default(0.7);
            $table->decimal('dien_tich_san_dao_tao', 15, 2)->default(0);
            $table->string('dia_chi', 500)->nullable();
            $table->boolean('is_tong')->default(false);
            $table->integer('thu_tu')->default(0);
            $table->timestamps();
        });

        // Báo cáo 3D: Hạ tầng công nghệ thông tin
        Schema::create('bc_ha_tang_cntts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dot_bao_cao_id')->constrained('dot_bao_caos')->cascadeOnDelete();
            $table->integer('stt')->default(0);
            $table->string('chi_so_thong_ke', 500);
            $table->decimal('gia_tri', 15, 2)->default(0);
            $table->string('don_vi', 50)->nullable();
            $table->text('ghi_chu')->nullable();
            $table->integer('thu_tu')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bc_ha_tang_cntts');
        Schema::dropIfExists('bc_cong_trinh_dao_taos');
        Schema::dropIfExists('bc_khuon_viens');
        Schema::dropIfExists('bc_tieu_chuan_csvcs');
        Schema::dropIfExists('bc_loai_phongs');
        Schema::dropIfExists('dot_bao_caos');
    }
};
