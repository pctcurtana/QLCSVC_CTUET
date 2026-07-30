<?php

namespace App\Http\Controllers;

use App\Services\ThongKeSnapshotService;
use App\Services\ThongKeService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThongKeController extends Controller
{
    protected $snapshotService;
    protected $thongKeService;

    public function __construct(ThongKeSnapshotService $snapshotService, ThongKeService $thongKeService)
    {
        $this->snapshotService = $snapshotService;
        $this->thongKeService  = $thongKeService;
    }

    public function index()
    {
        try {
            // Danh sách cơ sở để filter
            $danhSachCoSo = DB::table('co_sos')
                ->where('trang_thai_du_lieu', 'hien_hanh')
                ->select('id', 'ten_co_so')
                ->orderBy('ten_co_so')
                ->get();

            // Danh sách khu nhà để filter (kèm co_so_id)
            $danhSachKhuNha = DB::table('khu_nhas')
                ->where('trang_thai_du_lieu', 'hien_hanh')
                ->select('id', 'ten_khu_nha', 'co_so_id')
                ->orderBy('ten_khu_nha')
                ->get();

            // Danh sách phòng để filter (kèm khu_nha_id)
            $danhSachPhong = DB::table('phongs')
                ->where('trang_thai_du_lieu', 'hien_hanh')
                ->select('id', 'ten_phong', 'khu_nha_id')
                ->orderBy('ten_phong')
                ->get();

            // Đọc từ snapshot
            $thongKeCoSo = $this->snapshotService->getSnapshot('thongke.co_so');
            $thongKeKhuNha = $this->snapshotService->getSnapshot('thongke.khu_nha');
            $thongKePhong = $this->snapshotService->getSnapshot('thongke.phong');
            $thongKeThietBi = $this->snapshotService->getSnapshot('thongke.thiet_bi');

            return Inertia::render('ThongKe/Index', [
                'thongKeCoSo'    => $thongKeCoSo ?? [],
                'thongKeKhuNha'  => $thongKeKhuNha ?? [],
                'thongKePhong'   => $thongKePhong ?? [],
                'thongKeThietBi' => $thongKeThietBi ?? [],
                'danhSachCoSo'   => $danhSachCoSo,
                'danhSachKhuNha' => $danhSachKhuNha,
                'danhSachPhong'  => $danhSachPhong,
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải thống kê: ' . $e->getMessage());
        }
    }

    /**
     * Lấy dữ liệu snapshot theo danh sách keys được truyền lên.
     */
    public function getSnapshots(Request $request)
    {
        try {
            $keysParam = $request->query('keys');
            $keys = [];
            if (is_string($keysParam)) {
                $keys = array_filter(explode(',', $keysParam));
            } elseif (is_array($keysParam)) {
                $keys = $keysParam;
            }

            if (empty($keys)) {
                $keys = ThongKeSnapshotService::ALL_KEYS;
            }

            $snapshots = $this->snapshotService->getSnapshots($keys);

            return response()->json([
                'success'   => true,
                'snapshots' => $snapshots,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy snapshot: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tính lại tất cả snapshot thống kê từ database gốc.
     */
    public function recalculate()
    {
        try {
            $this->snapshotService->recalculateAll();
            return response()->json([
                'success' => true,
                'message' => 'Đã tính lại toàn bộ thống kê thành công.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tính lại thống kê: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Phân trang chi tiết Phòng cho trang Thống kê.
     * GET /thong-ke/chi-tiet-phong
     */
    public function chiTietPhong(Request $request)
    {
        try {
            $filters = $request->only(['search', 'co_so_id', 'khu_nha_id']);
            $perPage = (int) $request->input('per_page', 10);

            $data = $this->thongKeService->paginateChiTietPhong($filters, $perPage);

            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết phòng: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Phân trang chi tiết Thiết bị cho trang Thống kê.
     * GET /thong-ke/chi-tiet-thiet-bi
     */
    public function chiTietThietBi(Request $request)
    {
        try {
            $filters = $request->only(['search', 'co_so_id', 'khu_nha_id', 'phong_id']);
            $perPage = (int) $request->input('per_page', 10);

            $data = $this->thongKeService->paginateChiTietThietBi($filters, $perPage);

            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết thiết bị: ' . $e->getMessage(),
            ], 500);
        }
    }
}
