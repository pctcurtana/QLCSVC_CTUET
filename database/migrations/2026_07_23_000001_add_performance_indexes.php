<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm index dựa trên phân tích query thực tế.
 *
 * Mục tiêu:
 *   - Tăng tốc import/upsert (updateOrCreate theo ma_xxx + trang_thai_du_lieu)
 *   - Tăng tốc cascade FK update trong versioning (SCD Type 2)
 *   - Tăng tốc paginate filter + sort
 *   - Tăng tốc tra cứu báo cáo sự cố theo thiết bị
 *   - Tăng tốc thống kê GROUP BY (snapshot recalculate)
 *
 * TẠM HOÃN (sẽ thêm ở migration sau nếu cần):
 *   - lich_su_bao_duongs: (thiet_bi_id, ngay_bao_duong)
 *   - bao_cao_su_cos: (trang_thai, created_at) cho paginate
 *
 * KHÔNG sửa migration cũ. KHÔNG thay đổi logic nghiệp vụ.
 * KHÔNG drop FK index đơn cột hiện có.
 */
class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        // ─── co_sos ───────────────────────────────────────
        Schema::table('co_sos', function (Blueprint $table) {
            // Import upsert + preload FK map:
            // WHERE ma_co_so = ? AND trang_thai_du_lieu = 'hien_hanh'
            $table->index(['ma_co_so', 'trang_thai_du_lieu'], 'idx_co_sos_ma_ttdl');
        });

        // ─── khu_nhas ─────────────────────────────────────
        Schema::table('khu_nhas', function (Blueprint $table) {
            // Import upsert + preload FK map:
            // WHERE ma_khu_nha = ? AND trang_thai_du_lieu = 'hien_hanh'
            $table->index(['ma_khu_nha', 'trang_thai_du_lieu'], 'idx_khu_nhas_ma_ttdl');

            // Cascade FK update + getByCoSo() filter:
            // WHERE co_so_id = ? AND trang_thai_du_lieu = 'hien_hanh'
            $table->index(['co_so_id', 'trang_thai_du_lieu'], 'idx_khu_nhas_coso_ttdl');
        });

        // ─── phongs ──────────────────────────────────────
        Schema::table('phongs', function (Blueprint $table) {
            // Import upsert + preload FK map:
            // WHERE ma_phong = ? AND trang_thai_du_lieu = 'hien_hanh'
            $table->index(['ma_phong', 'trang_thai_du_lieu'], 'idx_phongs_ma_ttdl');

            // Cascade FK update + getByKhuNha() filter:
            // WHERE khu_nha_id = ? AND trang_thai_du_lieu = 'hien_hanh'
            $table->index(['khu_nha_id', 'trang_thai_du_lieu'], 'idx_phongs_khunha_ttdl');

            // Thống kê GROUP BY loai_phong + BaoCaoBgd filter:
            // WHERE trang_thai_du_lieu = 'hien_hanh' GROUP BY loai_phong
            $table->index(['trang_thai_du_lieu', 'loai_phong'], 'idx_phongs_ttdl_loai');
        });

        // ─── thiet_bis ───────────────────────────────────
        Schema::table('thiet_bis', function (Blueprint $table) {
            // Import upsert + preload FK map (bảng lớn nhất):
            // WHERE ma_thiet_bi = ? AND trang_thai_du_lieu = 'hien_hanh'
            $table->index(['ma_thiet_bi', 'trang_thai_du_lieu'], 'idx_thiet_bis_ma_ttdl');

            // getByPhong() + getGroupedByPhong() + paginate filter:
            // WHERE phong_id = ? AND trang_thai_du_lieu = 'hien_hanh'
            $table->index(['phong_id', 'trang_thai_du_lieu'], 'idx_thiet_bis_phong_ttdl');

            // Thiết bị cần bảo dưỡng (dashboard + paginate filter):
            // WHERE trang_thai_du_lieu = 'hien_hanh' AND ngay_bao_duong_tiep_theo <= NOW()
            $table->index(['trang_thai_du_lieu', 'ngay_bao_duong_tiep_theo'], 'idx_thiet_bis_ttdl_bdtt');

            // Thống kê GROUP BY loai_thiet_bi:
            // WHERE trang_thai_du_lieu = 'hien_hanh' GROUP BY loai_thiet_bi
            $table->index(['trang_thai_du_lieu', 'loai_thiet_bi'], 'idx_thiet_bis_ttdl_loai');
        });

        // ─── bao_cao_su_cos ──────────────────────────────
        Schema::table('bao_cao_su_cos', function (Blueprint $table) {
            // hasOpenReportForDevice + completeReports + updateStatusForDevice:
            // WHERE thiet_bi_id = ? AND trang_thai IN ('yeu_cau_sua_chua', 'dang_sua_chua')
            $table->index(['thiet_bi_id', 'trang_thai'], 'idx_bcsc_thietbi_trangthai');
        });
    }

    public function down()
    {
        Schema::table('co_sos', function (Blueprint $table) {
            $table->dropIndex('idx_co_sos_ma_ttdl');
        });

        Schema::table('khu_nhas', function (Blueprint $table) {
            $table->dropIndex('idx_khu_nhas_ma_ttdl');
            $table->dropIndex('idx_khu_nhas_coso_ttdl');
        });

        Schema::table('phongs', function (Blueprint $table) {
            $table->dropIndex('idx_phongs_ma_ttdl');
            $table->dropIndex('idx_phongs_khunha_ttdl');
            $table->dropIndex('idx_phongs_ttdl_loai');
        });

        Schema::table('thiet_bis', function (Blueprint $table) {
            $table->dropIndex('idx_thiet_bis_ma_ttdl');
            $table->dropIndex('idx_thiet_bis_phong_ttdl');
            $table->dropIndex('idx_thiet_bis_ttdl_bdtt');
            $table->dropIndex('idx_thiet_bis_ttdl_loai');
        });

        Schema::table('bao_cao_su_cos', function (Blueprint $table) {
            $table->dropIndex('idx_bcsc_thietbi_trangthai');
        });
    }
}
