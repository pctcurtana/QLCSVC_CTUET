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
            return Inertia::render('ThongKe/Index', [
                'thongKeCoSo'    => $this->thongKeService->getThongKeCoSo(),
                'thongKeKhuNha'  => $this->thongKeService->getThongKeKhuNha(),
                'thongKePhong'   => $this->thongKeService->getThongKePhong(),
                'thongKeThietBi' => $this->thongKeService->getThongKeThietBi(),
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải thống kê: ' . $e->getMessage());
        }
    }
}
