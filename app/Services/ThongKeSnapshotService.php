<?php

namespace App\Services;

use App\Events\ThongKeUpdated;
use App\Models\ThongKeSnapshot;
use App\Models\CoSo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service trung tâm quản lý snapshot thống kê.
 *
 * - Tính toán dữ liệu thống kê từ các bảng gốc (idempotent).
 * - Ghi kết quả vào bảng thong_ke_snapshots dạng key–value JSON.
 * - Xác định các key bị ảnh hưởng khi entity thay đổi.
 * - Broadcast event khi snapshot được cập nhật thành công.
 *
 * Logic tính toán được copy chính xác từ DashboardService và ThongKeService.
 * Không thay đổi công thức hay điều kiện lọc.
 */
class ThongKeSnapshotService
{
    /**
     * Tất cả key thống kê hợp lệ.
     */
    public const ALL_KEYS = [
        'dashboard.overview',
        'dashboard.loai_phong',
        'dashboard.trang_thai_phong',
        'dashboard.co_so',
        'dashboard.loai_thiet_bi',
        'thongke.co_so',
        'thongke.khu_nha',
        'thongke.phong',
        'thongke.thiet_bi',
    ];

    /**
     * Mapping: entity → danh sách key bị ảnh hưởng.
     */
    protected const ENTITY_KEY_MAP = [
        'co_so' => [
            'dashboard.overview',
            'dashboard.co_so',
            'thongke.co_so',
        ],
        'khu_nha' => [
            'dashboard.overview',
            'dashboard.co_so',
            'thongke.co_so',
            'thongke.khu_nha',
        ],
        'phong' => [
            'dashboard.overview',
            'dashboard.loai_phong',
            'dashboard.trang_thai_phong',
            'thongke.co_so',
            'thongke.khu_nha',
            'thongke.phong',
        ],
        'thiet_bi' => [
            'dashboard.overview',
            'dashboard.loai_thiet_bi',
            'thongke.co_so',
            'thongke.khu_nha',
            'thongke.phong',
            'thongke.thiet_bi',
        ],
        'lich_su_bao_duong' => [
            'thongke.thiet_bi',
        ],
    ];

    /**
     * Mapping: key → compute method.
     */
    protected const KEY_COMPUTE_MAP = [
        'dashboard.overview'       => 'computeDashboardOverview',
        'dashboard.loai_phong'     => 'computeLoaiPhong',
        'dashboard.trang_thai_phong' => 'computeTrangThaiPhong',
        'dashboard.co_so'          => 'computeCoSo',
        'dashboard.loai_thiet_bi'  => 'computeLoaiThietBi',
        'thongke.co_so'            => 'computeThongKeCoSo',
        'thongke.khu_nha'          => 'computeThongKeKhuNha',
        'thongke.phong'            => 'computeThongKePhong',
        'thongke.thiet_bi'         => 'computeThongKeThietBi',
    ];

    // ─────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────

    /**
     * Lấy danh sách key bị ảnh hưởng khi entity thay đổi.
     */
    public function getAffectedKeys(string $entity): array
    {
        return self::ENTITY_KEY_MAP[$entity] ?? [];
    }

    /**
     * Tính lại 1 key. Giữ value cũ nếu tính lỗi. Trả về true nếu thành công.
     */
    public function recalculateKey(string $key): bool
    {
        $method = self::KEY_COMPUTE_MAP[$key] ?? null;
        if (!$method) {
            Log::warning("ThongKeSnapshotService: Unknown key [{$key}]");
            return false;
        }

        // Set status = processing (giữ value cũ)
        ThongKeSnapshot::updateOrCreate(
            ['key' => $key],
            ['status' => 'processing']
        );

        try {
            $value = $this->{$method}();

            ThongKeSnapshot::updateOrCreate(
                ['key' => $key],
                [
                    'value'         => $value,
                    'status'        => 'ready',
                    'calculated_at' => now(),
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::error("ThongKeSnapshotService: Failed to compute [{$key}]: {$e->getMessage()}", [
                'exception' => $e,
            ]);

            // Giữ value cũ, chỉ đổi status = failed
            ThongKeSnapshot::where('key', $key)->update(['status' => 'failed']);

            return false;
        }
    }

    /**
     * Tính lại nhiều key. Chỉ broadcast 1 lần cuối.
     */
    public function recalculateKeys(array $keys): void
    {
        $uniqueKeys = array_unique($keys);
        $successKeys = [];

        foreach ($uniqueKeys as $key) {
            if ($this->recalculateKey($key)) {
                $successKeys[] = $key;
            }
        }

        if (!empty($successKeys)) {
            $this->broadcastUpdatedKeys($successKeys);
        }
    }

    /**
     * Tính lại tất cả key. Broadcast 1 lần cuối.
     */
    public function recalculateAll(): void
    {
        $this->recalculateKeys(self::ALL_KEYS);
    }

    /**
     * Gọi sau khi entity thay đổi (CRUD).
     * Xác định keys bị ảnh hưởng → tính lại → broadcast.
     *
     * Nếu tính lỗi → KHÔNG ảnh hưởng CRUD chính (đã commit xong).
     */
    public function onEntityChanged(string $entity): void
    {
        try {
            $keys = $this->getAffectedKeys($entity);
            if (!empty($keys)) {
                $this->recalculateKeys($keys);
            }
        } catch (\Throwable $e) {
            // Snapshot lỗi không được ảnh hưởng CRUD chính
            Log::error("ThongKeSnapshotService::onEntityChanged [{$entity}] failed: {$e->getMessage()}", [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Gọi sau khi import batch thành công.
     * Nhận danh sách entity đã thay đổi → gộp keys → tính lại 1 lần.
     */
    public function onBulkEntityChanged(array $entities): void
    {
        try {
            $allKeys = [];
            foreach ($entities as $entity) {
                $allKeys = array_merge($allKeys, $this->getAffectedKeys($entity));
            }
            $allKeys = array_unique($allKeys);

            if (!empty($allKeys)) {
                $this->recalculateKeys($allKeys);
            }
        } catch (\Throwable $e) {
            Log::error("ThongKeSnapshotService::onBulkEntityChanged failed: {$e->getMessage()}", [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Đọc snapshot 1 key.
     *
     * - Nếu snapshot tồn tại (kể cả status=failed): trả value + status hiện tại.
     * - Nếu snapshot chưa từng khởi tạo: tính trực tiếp, lưu, trả kết quả.
     */
    public function getSnapshot(string $key): ?array
    {
        $snapshot = ThongKeSnapshot::where('key', $key)->first();

        if ($snapshot) {
            return $snapshot->value;
        }

        // Chưa từng khởi tạo → tính trực tiếp và lưu
        $method = self::KEY_COMPUTE_MAP[$key] ?? null;
        if (!$method) {
            return null;
        }

        try {
            $value = $this->{$method}();
            ThongKeSnapshot::create([
                'key'           => $key,
                'value'         => $value,
                'status'        => 'ready',
                'calculated_at' => now(),
            ]);
            return $value;
        } catch (\Throwable $e) {
            Log::error("ThongKeSnapshotService::getSnapshot [{$key}] fallback failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Đọc nhiều snapshot. Trả về: key => value.
     */
    public function getSnapshots(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->getSnapshot($key);
        }
        return $result;
    }

    /**
     * Lấy status của snapshot.
     */
    public function getSnapshotStatus(string $key): ?string
    {
        return ThongKeSnapshot::where('key', $key)->value('status');
    }

    // ─────────────────────────────────────────────
    // BROADCAST
    // ─────────────────────────────────────────────

    /**
     * Broadcast danh sách key đã cập nhật thành công.
     */
    protected function broadcastUpdatedKeys(array $keys): void
    {
        try {
            $uniqueKeys = array_values(array_unique($keys));
            if (!empty($uniqueKeys)) {
                broadcast(new ThongKeUpdated($uniqueKeys));
            }
        } catch (\Throwable $e) {
            Log::error("ThongKeSnapshotService::broadcast failed: {$e->getMessage()}", [
                'exception' => $e,
            ]);
        }
    }

    // ─────────────────────────────────────────────
    // COMPUTE METHODS — DASHBOARD
    // Logic copy chính xác từ DashboardService
    // ─────────────────────────────────────────────

    /**
     * Dashboard overview KPI: tổng cơ sở, khu nhà, phòng, thiết bị, giá trị, diện tích.
     * Logic gốc: DashboardService::getOverviewStatistics()
     */
    protected function computeDashboardOverview(): array
    {
        $base = "trang_thai_du_lieu = 'hien_hanh'";

        $tongCoSo = DB::table('co_sos')->whereRaw($base)->count();
        $tongKhuNha = DB::table('khu_nhas')->whereRaw($base)->count();
        $tongPhong = DB::table('phongs')->whereRaw($base)->count();
        $tongThietBi = DB::table('thiet_bis')->whereRaw($base)->count();
        $tongGiaTriThietBi = (float) (DB::table('thiet_bis')->whereRaw($base)->sum('gia_tri') ?? 0);

        $dienTichDat = (float) (DB::table('co_sos')->whereRaw($base)->sum('dien_tich_dat') ?? 0);
        $viTriKhuonVienTb = (float) (DB::table('co_sos')->whereRaw($base)->avg('vi_tri_khuon_vien') ?? 0);
        $dienTichQuyDoi = (float) (DB::table('co_sos')->whereRaw($base)->sum('dien_tich_quy_doi') ?? 0);

        return [
            'tong_co_so'            => $tongCoSo,
            'tong_khu_nha'          => $tongKhuNha,
            'tong_phong'            => $tongPhong,
            'tong_thiet_bi'         => $tongThietBi,
            'tong_gia_tri_thiet_bi' => $tongGiaTriThietBi,
            'dien_tich_dat'         => $dienTichDat,
            'vi_tri_khuon_vien_tb'  => $viTriKhuonVienTb,
            'dien_tich_quy_doi'     => $dienTichQuyDoi,
        ];
    }

    /**
     * Dashboard: phân bố theo loại phòng.
     * Logic gốc: PhongRepository::getStatsByType()
     */
    protected function computeLoaiPhong(): array
    {
        return DB::table('phongs')
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->selectRaw('loai_phong, COUNT(*) as so_luong, SUM(dien_tich) as dien_tich')
            ->groupBy('loai_phong')
            ->get()
            ->map(fn($r) => [
                'loai_phong' => $r->loai_phong,
                'so_luong'   => (int) $r->so_luong,
                'dien_tich'  => (float) $r->dien_tich,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Dashboard: trạng thái phòng (donut chart).
     * Logic gốc: PhongRepository::getStatsByStatus()
     */
    protected function computeTrangThaiPhong(): array
    {
        return DB::table('phongs')
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->selectRaw('trang_thai, COUNT(*) as so_luong')
            ->groupBy('trang_thai')
            ->get()
            ->map(fn($r) => [
                'trang_thai' => $r->trang_thai,
                'so_luong'   => (int) $r->so_luong,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Dashboard: khu nhà theo cơ sở (RadialBar chart).
     * Logic gốc: DashboardService::getFacilityStatistics()
     */
    protected function computeCoSo(): array
    {
        return CoSo::where('trang_thai_du_lieu', 'hien_hanh')
            ->with(['khuNhas' => function ($q) {
                $q->where('trang_thai_du_lieu', 'hien_hanh');
            }])
            ->get()
            ->map(function ($coSo) {
                return [
                    'ten_co_so'   => $coSo->ten_co_so,
                    'so_khu_nha'  => $coSo->khuNhas->count(),
                    'dien_tich'   => (float) $coSo->dien_tich_dat,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Dashboard: thiết bị theo loại (Horizontal bar).
     * Logic gốc: ThietBiRepository::getStatsByType()
     */
    protected function computeLoaiThietBi(): array
    {
        return DB::table('thiet_bis')
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->selectRaw('loai_thiet_bi, COUNT(*) as so_luong, SUM(gia_tri) as gia_tri')
            ->groupBy('loai_thiet_bi')
            ->get()
            ->map(fn($r) => [
                'loai_thiet_bi' => $r->loai_thiet_bi,
                'so_luong'      => (int) $r->so_luong,
                'gia_tri'       => (float) $r->gia_tri,
            ])
            ->values()
            ->toArray();
    }

    // ─────────────────────────────────────────────
    // COMPUTE METHODS — THỐNG KÊ CHI TIẾT
    // Logic copy chính xác từ ThongKeService
    // ─────────────────────────────────────────────

    /**
     * Thống kê chi tiết: Cơ sở.
     * Logic gốc: ThongKeService::getThongKeCoSo()
     */
    protected function computeThongKeCoSo(): array
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

    /**
     * Thống kê chi tiết: Khu nhà.
     * Logic gốc: ThongKeService::getThongKeKhuNha()
     */
    protected function computeThongKeKhuNha(): array
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

    /**
     * Thống kê chi tiết: Phòng.
     * Logic gốc: ThongKeService::getThongKePhong()
     */
    protected function computeThongKePhong(): array
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

    /**
     * Thống kê chi tiết: Thiết bị.
     * Logic gốc: ThongKeService::getThongKeThietBi()
     */
    protected function computeThongKeThietBi(): array
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
}
