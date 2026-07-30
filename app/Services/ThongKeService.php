<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ThongKeService
{
    // ─────────────────────────────────────────────
    // CƠ SỞ
    // ─────────────────────────────────────────────

    public function getThongKeCoSo(): array
    {
        $base = "trang_thai_du_lieu = 'hien_hanh'";

        $tongQuan = DB::table('co_sos')->whereRaw($base)->selectRaw("
            COUNT(*) as tong_co_so,
            SUM(dien_tich_dat) as tong_dien_tich_dat,
            AVG(dien_tich_dat) as avg_dien_tich_dat,
            SUM(dien_tich_dat * vi_tri_khuon_vien) as tong_dien_tich_quy_doi,
            SUM(CASE WHEN trang_thai = 'active' THEN 1 ELSE 0 END) as co_so_hoat_dong,
            SUM(CASE WHEN trang_thai = 'inactive' THEN 1 ELSE 0 END) as co_so_ngung
        ")->first();

        $theoTrangThai = DB::table('co_sos')->whereRaw($base)
            ->selectRaw("trang_thai, COUNT(*) as so_luong")
            ->groupBy('trang_thai')->get();

        $chiTiet = DB::table('co_sos as cs')
            ->whereRaw("cs.$base")
            ->leftJoin('khu_nhas as kn', function ($j) {
                $j->on('kn.co_so_id', '=', 'cs.id')
                  ->where('kn.trang_thai_du_lieu', 'hien_hanh');
            })
            ->leftJoin('phongs as p', function ($j) {
                $j->on('p.khu_nha_id', '=', 'kn.id')
                  ->where('p.trang_thai_du_lieu', 'hien_hanh');
            })
            ->leftJoin('thiet_bis as tb', function ($j) {
                $j->on('tb.phong_id', '=', 'p.id')
                  ->where('tb.trang_thai_du_lieu', 'hien_hanh');
            })
            ->selectRaw("
                cs.id, cs.ma_co_so, cs.ten_co_so, cs.dia_chi, cs.trang_thai,
                cs.dien_tich_dat, cs.vi_tri_khuon_vien,
                (cs.dien_tich_dat * cs.vi_tri_khuon_vien) as dien_tich_quy_doi,
                COUNT(DISTINCT kn.id) as so_khu_nha,
                COUNT(DISTINCT p.id) as so_phong,
                COUNT(DISTINCT tb.id) as so_thiet_bi,
                COALESCE(SUM(DISTINCT tb.gia_tri), 0) as tong_gia_tri_thiet_bi
            ")
            ->groupBy('cs.id','cs.ma_co_so','cs.ten_co_so','cs.dia_chi','cs.trang_thai',
                      'cs.dien_tich_dat','cs.vi_tri_khuon_vien')
            ->orderBy('cs.ten_co_so')
            ->get();

        // Bar chart: diện tích và số khu nhà theo cơ sở
        $bieu_do_dien_tich = $chiTiet->map(fn($r) => [
            'name'           => $r->ten_co_so,
            'dienTichDat'    => (float) $r->dien_tich_dat,
            'dienTichQuyDoi' => (float) $r->dien_tich_quy_doi,
        ]);

        $bieu_do_so_luong = $chiTiet->map(fn($r) => [
            'name'      => $r->ten_co_so,
            'soKhuNha'  => (int) $r->so_khu_nha,
            'soPhong'   => (int) $r->so_phong,
            'soThietBi' => (int) $r->so_thiet_bi,
        ]);

        $bieu_do_trang_thai = $theoTrangThai->map(fn($r) => [
            'name'  => $r->trang_thai === 'active' ? 'Hoạt động' : 'Không HĐ',
            'value' => (int) $r->so_luong,
        ]);

        return [
            'tong_quan'          => $tongQuan,
            'chi_tiet'           => $chiTiet,
            'bieu_do_dien_tich'  => $bieu_do_dien_tich,
            'bieu_do_so_luong'   => $bieu_do_so_luong,
            'bieu_do_trang_thai' => $bieu_do_trang_thai,
        ];
    }

    // ─────────────────────────────────────────────
    // KHU NHÀ
    // ─────────────────────────────────────────────

    public function getThongKeKhuNha(): array
    {
        $base = "trang_thai_du_lieu = 'hien_hanh'";

        $tongQuan = DB::table('khu_nhas')->whereRaw($base)->selectRaw("
            COUNT(*) as tong_khu_nha,
            SUM(tong_dien_tich_san) as tong_dien_tich_san,
            SUM(tong_dien_tich_san * he_so_su_dung_dao_tao) as tong_dt_dao_tao,
            AVG(so_tang) as avg_so_tang,
            SUM(CASE WHEN trang_thai = 'active' THEN 1 ELSE 0 END) as khu_nha_hoat_dong,
            SUM(CASE WHEN trang_thai = 'inactive' THEN 1 ELSE 0 END) as khu_nha_ngung
        ")->first();

        $theoLoai = DB::table('khu_nhas')->whereRaw($base)
            ->selectRaw("loai_khu_nha, COUNT(*) as so_luong, SUM(tong_dien_tich_san) as tong_dt")
            ->groupBy('loai_khu_nha')->get();

        $theoTrangThai = DB::table('khu_nhas')->whereRaw($base)
            ->selectRaw("trang_thai, COUNT(*) as so_luong")
            ->groupBy('trang_thai')->get();

        $chiTiet = DB::table('khu_nhas as kn')
            ->whereRaw("kn.$base")
            ->leftJoin('co_sos as cs', 'cs.id', '=', 'kn.co_so_id')
            ->leftJoin('phongs as p', function ($j) {
                $j->on('p.khu_nha_id', '=', 'kn.id')
                  ->where('p.trang_thai_du_lieu', 'hien_hanh');
            })
            ->leftJoin('thiet_bis as tb', function ($j) {
                $j->on('tb.phong_id', '=', 'p.id')
                  ->where('tb.trang_thai_du_lieu', 'hien_hanh');
            })
            ->selectRaw("
                kn.id, kn.ma_khu_nha, kn.ten_khu_nha, kn.loai_khu_nha,
                kn.so_tang, kn.tong_dien_tich_san, kn.he_so_su_dung_dao_tao,
                kn.trang_thai, kn.nam_xay_dung, kn.co_so_id,
                (kn.tong_dien_tich_san * kn.he_so_su_dung_dao_tao) as dt_dao_tao,
                cs.ten_co_so,
                COUNT(DISTINCT p.id) as so_phong,
                COUNT(DISTINCT tb.id) as so_thiet_bi
            ")
            ->groupBy('kn.id','kn.ma_khu_nha','kn.ten_khu_nha','kn.loai_khu_nha',
                      'kn.so_tang','kn.tong_dien_tich_san','kn.he_so_su_dung_dao_tao',
                      'kn.trang_thai','kn.nam_xay_dung','kn.co_so_id','cs.ten_co_so')
            ->orderBy('cs.ten_co_so')->orderBy('kn.ten_khu_nha')
            ->get();

        $loaiLabels = [
            'phong_hoc'        => 'Phòng học',
            'phong_lam_viec'   => 'Phòng làm việc',
            'phong_chuc_nang'  => 'Phòng chức năng',
        ];

        $bieu_do_loai = $theoLoai->map(fn($r) => [
            'name'    => $loaiLabels[$r->loai_khu_nha] ?? $r->loai_khu_nha,
            'soLuong' => (int) $r->so_luong,
            'tongDT'  => (float) $r->tong_dt,
        ]);

        $bieu_do_dien_tich = $chiTiet->map(fn($r) => [
            'name'    => $r->ten_khu_nha,
            'sanXD'   => (float) $r->tong_dien_tich_san,
            'dtDaoTao'=> (float) $r->dt_dao_tao,
            'soPhong' => (int) $r->so_phong,
        ]);

        $bieu_do_trang_thai = $theoTrangThai->map(fn($r) => [
            'name'  => $r->trang_thai === 'active' ? 'Hoạt động' : 'Không HĐ',
            'value' => (int) $r->so_luong,
        ]);

        return [
            'tong_quan'          => $tongQuan,
            'chi_tiet'           => $chiTiet,
            'bieu_do_loai'       => $bieu_do_loai,
            'bieu_do_dien_tich'  => $bieu_do_dien_tich,
            'bieu_do_trang_thai' => $bieu_do_trang_thai,
        ];
    }

    // ─────────────────────────────────────────────
    // PHÒNG
    // ─────────────────────────────────────────────

    public function getThongKePhong(): array
    {
        $base = "trang_thai_du_lieu = 'hien_hanh'";

        $tongQuan = DB::table('phongs')->whereRaw($base)->selectRaw("
            COUNT(*) as tong_phong,
            SUM(dien_tich) as tong_dien_tich,
            AVG(dien_tich) as avg_dien_tich,
            SUM(suc_chua) as tong_suc_chua,
            AVG(suc_chua) as avg_suc_chua,
            SUM(CASE WHEN trang_thai = 'active' THEN 1 ELSE 0 END) as phong_hoat_dong,
            SUM(CASE WHEN trang_thai = 'maintenance' THEN 1 ELSE 0 END) as phong_bao_tri,
            SUM(CASE WHEN trang_thai = 'inactive' THEN 1 ELSE 0 END) as phong_ngung
        ")->first();

        $theoLoai = DB::table('phongs')->whereRaw($base)
            ->selectRaw("loai_phong, COUNT(*) as so_luong, SUM(dien_tich) as tong_dt, SUM(suc_chua) as tong_suc_chua")
            ->groupBy('loai_phong')->orderByDesc('so_luong')->get();

        $theoTrangThai = DB::table('phongs')->whereRaw($base)
            ->selectRaw("trang_thai, COUNT(*) as so_luong")
            ->groupBy('trang_thai')->get();

        $theoTang = DB::table('phongs')->whereRaw($base)
            ->selectRaw("tang, COUNT(*) as so_luong, SUM(dien_tich) as tong_dt")
            ->groupBy('tang')->orderBy('tang')->get();

        $chiTiet = DB::table('phongs as p')
            ->whereRaw("p.$base")
            ->leftJoin('khu_nhas as kn', 'kn.id', '=', 'p.khu_nha_id')
            ->leftJoin('co_sos as cs', 'cs.id', '=', 'kn.co_so_id')
            ->leftJoin('thiet_bis as tb', function ($j) {
                $j->on('tb.phong_id', '=', 'p.id')
                  ->where('tb.trang_thai_du_lieu', 'hien_hanh');
            })
            ->selectRaw("
                p.id, p.ma_phong, p.ten_phong, p.loai_phong, p.tang,
                p.dien_tich, p.suc_chua, p.trang_thai, p.khu_nha_id, kn.co_so_id,
                kn.ten_khu_nha, cs.ten_co_so,
                COUNT(DISTINCT tb.id) as so_thiet_bi,
                COALESCE(SUM(tb.gia_tri), 0) as tong_gia_tri_thiet_bi
            ")
            ->groupBy('p.id','p.ma_phong','p.ten_phong','p.loai_phong','p.tang',
                      'p.dien_tich','p.suc_chua','p.trang_thai','p.khu_nha_id','kn.co_so_id','kn.ten_khu_nha','cs.ten_co_so')
            ->orderBy('cs.ten_co_so')->orderBy('kn.ten_khu_nha')->orderBy('p.tang')->orderBy('p.ten_phong')
            ->get();

        $loaiLabels = [
            'phong_hoc'         => 'Phòng học',
            'phong_thi_nghiem'  => 'Phòng TN',
            'phong_thuc_hanh'   => 'Phòng TH',
            'phong_lam_viec'    => 'Phòng LV',
            'phong_chuc_nang'   => 'Phòng CN',
        ];

        $trangThaiLabels = [
            'active'      => 'Hoạt động',
            'maintenance' => 'Bảo trì',
            'inactive'    => 'Không HĐ',
        ];

        $bieu_do_loai = $theoLoai->map(fn($r) => [
            'name'      => $loaiLabels[$r->loai_phong] ?? $r->loai_phong,
            'soLuong'   => (int) $r->so_luong,
            'tongDT'    => (float) $r->tong_dt,
            'sucChua'   => (int) $r->tong_suc_chua,
        ]);

        $bieu_do_trang_thai = $theoTrangThai->map(fn($r) => [
            'name'  => $trangThaiLabels[$r->trang_thai] ?? $r->trang_thai,
            'value' => (int) $r->so_luong,
        ]);

        $bieu_do_tang = $theoTang->map(fn($r) => [
            'name'    => 'Tầng ' . $r->tang,
            'soPhong' => (int) $r->so_luong,
            'tongDT'  => (float) $r->tong_dt,
        ]);

        return [
            'tong_quan'          => $tongQuan,
            'chi_tiet'           => $chiTiet,
            'bieu_do_loai'       => $bieu_do_loai,
            'bieu_do_trang_thai' => $bieu_do_trang_thai,
            'bieu_do_tang'       => $bieu_do_tang,
        ];
    }

    // ─────────────────────────────────────────────
    // THIẾT BỊ
    // ─────────────────────────────────────────────

    public function getThongKeThietBi(): array
    {
        $base = "trang_thai_du_lieu = 'hien_hanh'";

        $tongQuan = DB::table('thiet_bis')->whereRaw($base)->selectRaw("
            COUNT(*) as tong_thiet_bi,
            SUM(gia_tri) as tong_gia_tri,
            AVG(gia_tri) as avg_gia_tri,
            MAX(gia_tri) as max_gia_tri,
            SUM(CASE WHEN trang_thai = 'tot' THEN 1 ELSE 0 END) as dang_hoat_dong,
            SUM(CASE WHEN trang_thai = 'can_sua_chua' THEN 1 ELSE 0 END) as can_sua_chua,
            SUM(CASE WHEN trang_thai = 'hu_hong' THEN 1 ELSE 0 END) as hu_hong,
            SUM(CASE WHEN ngay_bao_duong_tiep_theo IS NOT NULL AND ngay_bao_duong_tiep_theo <= CURDATE() THEN 1 ELSE 0 END) as can_bao_duong
        ")->first();

        $theoLoai = DB::table('thiet_bis')->whereRaw($base)
            ->selectRaw("loai_thiet_bi, COUNT(*) as so_luong, SUM(gia_tri) as tong_gia_tri")
            ->groupBy('loai_thiet_bi')->orderByDesc('so_luong')->get();

        $theoTrangThai = DB::table('thiet_bis')->whereRaw($base)
            ->selectRaw("trang_thai, COUNT(*) as so_luong")
            ->groupBy('trang_thai')->get();

        $theoNamMua = DB::table('thiet_bis')->whereRaw($base)
            ->whereNotNull('nam_mua')
            ->selectRaw("nam_mua, COUNT(*) as so_luong, SUM(gia_tri) as tong_gia_tri")
            ->groupBy('nam_mua')->orderBy('nam_mua')->get();

        $theoHang = DB::table('thiet_bis')->whereRaw($base)
            ->whereNotNull('hang_san_xuat')->where('hang_san_xuat', '!=', '')
            ->selectRaw("hang_san_xuat, COUNT(*) as so_luong, SUM(gia_tri) as tong_gia_tri")
            ->groupBy('hang_san_xuat')->orderByDesc('so_luong')->limit(10)->get();

        $chiTiet = DB::table('thiet_bis as tb')
            ->whereRaw("tb.$base")
            ->leftJoin('phongs as p', 'p.id', '=', 'tb.phong_id')
            ->leftJoin('khu_nhas as kn', 'kn.id', '=', 'p.khu_nha_id')
            ->leftJoin('co_sos as cs', 'cs.id', '=', 'kn.co_so_id')
            ->selectRaw("
                tb.id, tb.ma_thiet_bi, tb.ten_thiet_bi, tb.loai_thiet_bi,
                tb.hang_san_xuat, tb.model, tb.serial_number,
                tb.nam_san_xuat, tb.nam_mua, tb.gia_tri, tb.trang_thai,
                tb.ngay_bao_duong_tiep_theo, tb.phong_id, p.khu_nha_id, kn.co_so_id,
                (CASE WHEN tb.ngay_bao_duong_tiep_theo IS NOT NULL AND tb.ngay_bao_duong_tiep_theo <= CURDATE() THEN 1 ELSE 0 END) as qua_han_bao_duong,
                p.ten_phong, kn.ten_khu_nha, cs.ten_co_so
            ")
            ->orderBy('cs.ten_co_so')->orderBy('kn.ten_khu_nha')->orderBy('p.ten_phong')->orderBy('tb.ten_thiet_bi')
            ->get();

        $loaiLabels = [
            'van_phong'  => 'Văn phòng',
            'day_hoc'    => 'Dạy học',
            'thi_nghiem' => 'Thí nghiệm',
            'thuc_hanh'  => 'Thực hành',
        ];

        $trangThaiLabels = [
            'tot'          => 'Tốt',
            'can_sua_chua' => 'Cần sửa chữa',
            'hu_hong'      => 'Hư hỏng',
        ];

        $bieu_do_loai = $theoLoai->map(fn($r) => [
            'name'      => $loaiLabels[$r->loai_thiet_bi] ?? $r->loai_thiet_bi,
            'soLuong'   => (int) $r->so_luong,
            'tongGiaTri'=> (float) $r->tong_gia_tri,
        ]);

        $bieu_do_trang_thai = $theoTrangThai->map(fn($r) => [
            'name'  => $trangThaiLabels[$r->trang_thai] ?? $r->trang_thai,
            'value' => (int) $r->so_luong,
        ]);

        $bieu_do_nam_mua = $theoNamMua->map(fn($r) => [
            'name'       => (string) $r->nam_mua,
            'soLuong'    => (int) $r->so_luong,
            'tongGiaTri' => (float) $r->tong_gia_tri,
        ]);

        $bieu_do_hang = $theoHang->map(fn($r) => [
            'name'    => $r->hang_san_xuat,
            'soLuong' => (int) $r->so_luong,
        ]);

        return [
            'tong_quan'          => $tongQuan,
            'chi_tiet'           => $chiTiet,
            'bieu_do_loai'       => $bieu_do_loai,
            'bieu_do_trang_thai' => $bieu_do_trang_thai,
            'bieu_do_nam_mua'    => $bieu_do_nam_mua,
            'bieu_do_hang'       => $bieu_do_hang,
        ];
    }

    // ─────────────────────────────────────────────
    // PHÂN TRANG CHI TIẾT (cho bảng ThongKe)
    // ─────────────────────────────────────────────

    /**
     * Phân trang chi tiết Phòng cho trang Thống kê + thống kê tổng quan theo bộ lọc.
     *
     * @param array $filters  Keys: search, co_so_id, khu_nha_id
     * @param int   $perPage
     * @return array
     */
    public function paginateChiTietPhong(array $filters = [], int $perPage = 10): array
    {
        $base = "trang_thai_du_lieu = 'hien_hanh'";

        $baseQuery = DB::table('phongs as p')
            ->whereRaw("p.$base")
            ->leftJoin('khu_nhas as kn', 'kn.id', '=', 'p.khu_nha_id')
            ->leftJoin('co_sos as cs', 'cs.id', '=', 'kn.co_so_id');

        if (!empty($filters['co_so_id'])) {
            $baseQuery->where('kn.co_so_id', $filters['co_so_id']);
        }
        if (!empty($filters['khu_nha_id'])) {
            $baseQuery->where('p.khu_nha_id', $filters['khu_nha_id']);
        }

        // Summary KPI
        $tongQuan = (clone $baseQuery)->selectRaw("
            COUNT(*) as tong_phong,
            COALESCE(SUM(p.dien_tich), 0) as tong_dien_tich,
            COALESCE(SUM(p.suc_chua), 0) as tong_suc_chua,
            COALESCE(SUM(CASE WHEN p.trang_thai = 'maintenance' THEN 1 ELSE 0 END), 0) as phong_bao_tri
        ")->first();

        // Biểu đồ loại
        $theoLoai = (clone $baseQuery)
            ->selectRaw("p.loai_phong, COUNT(*) as so_luong, COALESCE(SUM(p.suc_chua), 0) as tong_suc_chua")
            ->groupBy('p.loai_phong')->orderByDesc('so_luong')->get();

        $loaiLabels = [
            'phong_hoc'         => 'Phòng học',
            'phong_thi_nghiem'  => 'Phòng TN',
            'phong_thuc_hanh'   => 'Phòng TH',
            'phong_lam_viec'    => 'Phòng LV',
            'phong_chuc_nang'   => 'Phòng CN',
        ];
        $bieuDoLoai = $theoLoai->map(fn($r) => [
            'name'    => $loaiLabels[$r->loai_phong] ?? $r->loai_phong,
            'soLuong' => (int) $r->so_luong,
            'sucChua' => (int) $r->tong_suc_chua,
        ]);

        // Biểu đồ trạng thái
        $theoTrangThai = (clone $baseQuery)
            ->selectRaw("p.trang_thai, COUNT(*) as so_luong")
            ->groupBy('p.trang_thai')->get();

        $trangThaiLabels = [
            'active'      => 'Hoạt động',
            'maintenance' => 'Bảo trì',
            'inactive'    => 'Không HĐ',
        ];
        $bieuDoTrangThai = $theoTrangThai->map(fn($r) => [
            'name'  => $trangThaiLabels[$r->trang_thai] ?? $r->trang_thai,
            'value' => (int) $r->so_luong,
        ]);

        // Biểu đồ tầng
        $theoTang = (clone $baseQuery)
            ->selectRaw("p.tang, COUNT(*) as so_luong, COALESCE(SUM(p.dien_tich), 0) as tong_dt")
            ->groupBy('p.tang')->orderBy('p.tang')->get();

        $bieuDoTang = $theoTang->map(fn($r) => [
            'name'    => 'Tầng ' . $r->tang,
            'soPhong' => (int) $r->so_luong,
            'tongDT'  => (float) $r->tong_dt,
        ]);

        // Paginator table query
        $query = (clone $baseQuery)
            ->leftJoin('thiet_bis as tb', function ($j) {
                $j->on('tb.phong_id', '=', 'p.id')
                  ->where('tb.trang_thai_du_lieu', 'hien_hanh');
            })
            ->selectRaw("
                p.id, p.ma_phong, p.ten_phong, p.loai_phong, p.tang,
                p.dien_tich, p.suc_chua, p.trang_thai, p.khu_nha_id, kn.co_so_id,
                kn.ten_khu_nha, cs.ten_co_so,
                COUNT(DISTINCT tb.id) as so_thiet_bi,
                COALESCE(SUM(tb.gia_tri), 0) as tong_gia_tri_thiet_bi
            ")
            ->groupBy('p.id','p.ma_phong','p.ten_phong','p.loai_phong','p.tang',
                      'p.dien_tich','p.suc_chua','p.trang_thai','p.khu_nha_id','kn.co_so_id','kn.ten_khu_nha','cs.ten_co_so');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('p.ma_phong', 'like', "%{$search}%")
                  ->orWhere('p.ten_phong', 'like', "%{$search}%")
                  ->orWhere('kn.ten_khu_nha', 'like', "%{$search}%")
                  ->orWhere('cs.ten_co_so', 'like', "%{$search}%");
            });
        }

        $paginator = $query
            ->orderBy('cs.ten_co_so')
            ->orderBy('kn.ten_khu_nha')
            ->orderBy('p.tang')
            ->orderBy('p.ten_phong')
            ->paginate($perPage);

        return [
            'paginator'          => $paginator,
            'tong_quan'          => $tongQuan,
            'bieu_do_loai'       => $bieuDoLoai,
            'bieu_do_trang_thai' => $bieuDoTrangThai,
            'bieu_do_tang'       => $bieuDoTang,
        ];
    }

    /**
     * Phân trang chi tiết Thiết bị cho trang Thống kê + thống kê tổng quan theo bộ lọc.
     *
     * @param array $filters  Keys: search, co_so_id, khu_nha_id, phong_id
     * @param int   $perPage
     * @return array
     */
    public function paginateChiTietThietBi(array $filters = [], int $perPage = 10): array
    {
        $base = "trang_thai_du_lieu = 'hien_hanh'";

        $baseQuery = DB::table('thiet_bis as tb')
            ->whereRaw("tb.$base")
            ->leftJoin('phongs as p', 'p.id', '=', 'tb.phong_id')
            ->leftJoin('khu_nhas as kn', 'kn.id', '=', 'p.khu_nha_id')
            ->leftJoin('co_sos as cs', 'cs.id', '=', 'kn.co_so_id');

        if (!empty($filters['co_so_id'])) {
            $baseQuery->where('kn.co_so_id', $filters['co_so_id']);
        }
        if (!empty($filters['khu_nha_id'])) {
            $baseQuery->where('p.khu_nha_id', $filters['khu_nha_id']);
        }
        if (!empty($filters['phong_id'])) {
            $baseQuery->where('tb.phong_id', $filters['phong_id']);
        }

        // Summary KPI
        $tongQuan = (clone $baseQuery)->selectRaw("
            COUNT(*) as tong_thiet_bi,
            COALESCE(SUM(tb.gia_tri), 0) as tong_gia_tri,
            COALESCE(SUM(CASE WHEN tb.trang_thai = 'tot' THEN 1 ELSE 0 END), 0) as dang_hoat_dong,
            COALESCE(SUM(CASE WHEN tb.trang_thai = 'can_sua_chua' THEN 1 ELSE 0 END), 0) as can_sua_chua,
            COALESCE(SUM(CASE WHEN tb.trang_thai = 'hu_hong' THEN 1 ELSE 0 END), 0) as hu_hong,
            COALESCE(SUM(CASE WHEN tb.ngay_bao_duong_tiep_theo IS NOT NULL AND tb.ngay_bao_duong_tiep_theo <= CURDATE() THEN 1 ELSE 0 END), 0) as can_bao_duong
        ")->first();

        // Biểu đồ loại
        $theoLoai = (clone $baseQuery)
            ->selectRaw("tb.loai_thiet_bi, COUNT(*) as so_luong, COALESCE(SUM(tb.gia_tri), 0) as tong_gia_tri")
            ->groupBy('tb.loai_thiet_bi')->orderByDesc('so_luong')->get();

        $loaiLabels = [
            'van_phong'  => 'Văn phòng',
            'day_hoc'    => 'Dạy học',
            'thi_nghiem' => 'Thí nghiệm',
            'thuc_hanh'  => 'Thực hành',
        ];
        $bieuDoLoai = $theoLoai->map(fn($r) => [
            'name'       => $loaiLabels[$r->loai_thiet_bi] ?? $r->loai_thiet_bi,
            'soLuong'    => (int) $r->so_luong,
            'tongGiaTri' => (float) $r->tong_gia_tri,
        ]);

        // Biểu đồ trạng thái
        $theoTrangThai = (clone $baseQuery)
            ->selectRaw("tb.trang_thai, COUNT(*) as so_luong")
            ->groupBy('tb.trang_thai')->get();

        $trangThaiLabels = [
            'tot'          => 'Tốt',
            'can_sua_chua' => 'Cần sửa chữa',
            'hu_hong'      => 'Hư hỏng',
        ];
        $bieuDoTrangThai = $theoTrangThai->map(fn($r) => [
            'name'  => $trangThaiLabels[$r->trang_thai] ?? $r->trang_thai,
            'value' => (int) $r->so_luong,
        ]);

        // Biểu đồ năm mua
        $theoNamMua = (clone $baseQuery)
            ->whereNotNull('tb.nam_mua')
            ->selectRaw("tb.nam_mua, COUNT(*) as so_luong, COALESCE(SUM(tb.gia_tri), 0) as tong_gia_tri")
            ->groupBy('tb.nam_mua')->orderBy('tb.nam_mua')->get();

        $bieuDoNamMua = $theoNamMua->map(fn($r) => [
            'name'       => (string) $r->nam_mua,
            'soLuong'    => (int) $r->so_luong,
            'tongGiaTri' => (float) $r->tong_gia_tri,
        ]);

        // Biểu đồ hãng
        $theoHang = (clone $baseQuery)
            ->whereNotNull('tb.hang_san_xuat')->where('tb.hang_san_xuat', '!=', '')
            ->selectRaw("tb.hang_san_xuat, COUNT(*) as so_luong")
            ->groupBy('tb.hang_san_xuat')->orderByDesc('so_luong')->limit(10)->get();

        $bieuDoHang = $theoHang->map(fn($r) => [
            'name'    => $r->hang_san_xuat,
            'soLuong' => (int) $r->so_luong,
        ]);

        // Paginator table query
        $query = (clone $baseQuery)
            ->selectRaw("
                tb.id, tb.ma_thiet_bi, tb.ten_thiet_bi, tb.loai_thiet_bi,
                tb.hang_san_xuat, tb.model, tb.serial_number,
                tb.nam_san_xuat, tb.nam_mua, tb.gia_tri, tb.trang_thai,
                tb.ngay_bao_duong_tiep_theo, tb.phong_id, p.khu_nha_id, kn.co_so_id,
                (CASE WHEN tb.ngay_bao_duong_tiep_theo IS NOT NULL AND tb.ngay_bao_duong_tiep_theo <= CURDATE() THEN 1 ELSE 0 END) as qua_han_bao_duong,
                p.ten_phong, kn.ten_khu_nha, cs.ten_co_so
            ");

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('tb.ma_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('tb.ten_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('tb.hang_san_xuat', 'like', "%{$search}%")
                  ->orWhere('tb.serial_number', 'like', "%{$search}%")
                  ->orWhere('p.ten_phong', 'like', "%{$search}%");
            });
        }

        $paginator = $query
            ->orderBy('cs.ten_co_so')
            ->orderBy('kn.ten_khu_nha')
            ->orderBy('p.ten_phong')
            ->orderBy('tb.ten_thiet_bi')
            ->paginate($perPage);

        return [
            'paginator'          => $paginator,
            'tong_quan'          => $tongQuan,
            'bieu_do_loai'       => $bieuDoLoai,
            'bieu_do_trang_thai' => $bieuDoTrangThai,
            'bieu_do_nam_mua'    => $bieuDoNamMua,
            'bieu_do_hang'       => $bieuDoHang,
        ];
    }
}
