<?php

namespace App\Http\Controllers;

use App\Services\ThongKeService;
use Inertia\Inertia;

class ThongKeController extends Controller
{
    protected $thongKeService;

    public function __construct(ThongKeService $thongKeService)
    {
        $this->thongKeService = $thongKeService;
    }

    public function index()
    {
        try {
            // Danh sách cơ sở để filter
            $danhSachCoSo = \DB::table('co_sos')
                ->where('trang_thai_du_lieu', 'hien_hanh')
                ->select('id', 'ten_co_so')
                ->orderBy('ten_co_so')
                ->get();

            // Danh sách khu nhà để filter (kèm co_so_id)
            $danhSachKhuNha = \DB::table('khu_nhas')
                ->where('trang_thai_du_lieu', 'hien_hanh')
                ->select('id', 'ten_khu_nha', 'co_so_id')
                ->orderBy('ten_khu_nha')
                ->get();

            // Danh sách phòng để filter (kèm khu_nha_id)
            $danhSachPhong = \DB::table('phongs')
                ->where('trang_thai_du_lieu', 'hien_hanh')
                ->select('id', 'ten_phong', 'khu_nha_id')
                ->orderBy('ten_phong')
                ->get();

            return Inertia::render('ThongKe/Index', [
                'thongKeCoSo'    => $this->thongKeService->getThongKeCoSo(),
                'thongKeKhuNha'  => $this->thongKeService->getThongKeKhuNha(),
                'thongKePhong'   => $this->thongKeService->getThongKePhong(),
                'thongKeThietBi' => $this->thongKeService->getThongKeThietBi(),
                'danhSachCoSo'   => $danhSachCoSo,
                'danhSachKhuNha' => $danhSachKhuNha,
                'danhSachPhong'  => $danhSachPhong,
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải thống kê: ' . $e->getMessage());
        }
    }
}
