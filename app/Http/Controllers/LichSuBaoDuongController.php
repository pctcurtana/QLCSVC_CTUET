<?php

namespace App\Http\Controllers;

use App\Services\LichSuBaoDuongService;
use App\Services\ThietBiService;
use App\Http\Requests\LichSuBaoDuong\StoreLichSuBaoDuongRequest;
use App\Http\Requests\LichSuBaoDuong\UpdateLichSuBaoDuongRequest;
use App\Models\ThietBi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LichSuBaoDuongController extends Controller
{
    /**
     * @var LichSuBaoDuongService
     */
    protected $lichSuBaoDuongService;

    /**
     * @var ThietBiService
     */
    protected $thietBiService;

    /**
     * LichSuBaoDuongController constructor.
     *
     * @param LichSuBaoDuongServiceInterface $lichSuBaoDuongService
     * @param ThietBiServiceInterface $thietBiService
     */
    public function __construct(
        LichSuBaoDuongService $lichSuBaoDuongService,
        ThietBiService $thietBiService
    ) {
        $this->lichSuBaoDuongService = $lichSuBaoDuongService;
        $this->thietBiService = $thietBiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['thiet_bi_id', 'loai_bao_duong', 'trang_thai']);
            $lichSuBaoDuongs = $this->lichSuBaoDuongService->getAllPaginated($filters, 10);
            $thietBis = $this->thietBiService->getActiveThietBis();

            return Inertia::render('LichSuBaoDuong/Index', [
                'lichSuBaoDuongs' => $lichSuBaoDuongs,
                'thietBis' => $thietBis,
                'filters' => $filters
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải danh sách lịch sử bảo dưỡng: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $thietBis = $this->thietBiService->getActiveThietBis();
            $thietBiId = $request->get('thiet_bi_id');

            return Inertia::render('LichSuBaoDuong/Create', [
                'thietBis' => $thietBis,
                'thietBiId' => $thietBiId,
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('lich-su-bao-duong.index')->with('error', 'Lỗi khi tải trang tạo mới: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLichSuBaoDuongRequest $request)
    {
        try {
            $this->lichSuBaoDuongService->create($request->validated());
            return redirect()->route('lich-su-bao-duong.index')->with('success', 'Thêm lịch sử bảo dưỡng thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi thêm lịch sử bảo dưỡng: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $lichSuBaoDuong = $this->lichSuBaoDuongService->getById($id);
            $thietBis = $this->thietBiService->getActiveThietBis();

            return Inertia::render('LichSuBaoDuong/Edit', [
                'lichSuBaoDuong' => $lichSuBaoDuong,
                'thietBis' => $thietBis,
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('lich-su-bao-duong.index')->with('error', 'Không tìm thấy lịch sử bảo dưỡng: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLichSuBaoDuongRequest $request, $id)
    {
        try {
            $this->lichSuBaoDuongService->update($id, $request->validated());
            return redirect()->route('lich-su-bao-duong.index')->with('success', 'Cập nhật lịch sử bảo dưỡng thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi cập nhật lịch sử bảo dưỡng: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->lichSuBaoDuongService->delete($id);
            return redirect()->route('lich-su-bao-duong.index')->with('success', 'Xóa lịch sử bảo dưỡng thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa lịch sử bảo dưỡng: ' . $e->getMessage());
        }
    }

    /**
     * Show maintenance history for a specific equipment
     */
    public function show(ThietBi $thietBi)
    {
        try {
            $lichSuBaoDuongs = $this->lichSuBaoDuongService->getByThietBiObject($thietBi);

            return Inertia::render('LichSuBaoDuong/Show', [
                'thietBi' => $thietBi->load('phong.khuNha.coSo'),
                'lichSuBaoDuongs' => $lichSuBaoDuongs,
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('lich-su-bao-duong.index')->with('error', 'Lỗi khi xem lịch sử bảo dưỡng: ' . $e->getMessage());
        }
    }
}
