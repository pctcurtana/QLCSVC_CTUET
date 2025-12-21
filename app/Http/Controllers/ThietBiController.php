<?php

namespace App\Http\Controllers;

use App\Services\PhongService;
use App\Services\ThietBiService;
use App\Http\Requests\ThietBi\StoreThietBiRequest;
use App\Http\Requests\ThietBi\UpdateThietBiRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThietBiController extends Controller
{
    /**
     * @var ThietBiService
     */
    protected $thietBiService;

    /**
     * @var PhongService
     */
    protected $phongService;

    /**
     * ThietBiController constructor.
     *
     * @param ThietBiServiceInterface $thietBiService
     * @param PhongServiceInterface $phongService
     */
    public function __construct(
        ThietBiService $thietBiService,
        PhongService $phongService
    ) {
        $this->thietBiService = $thietBiService;
        $this->phongService = $phongService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'phong_id', 'loai_thiet_bi', 'trang_thai', 'can_bao_duong']);
            $thietBis = $this->thietBiService->getAllPaginated($filters, 10);
            $phongs = $this->phongService->getActivePhongs();

            return Inertia::render('ThietBi/Index', [
                'thietBis' => $thietBis,
                'phongs' => $phongs,
                'filters' => $filters
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải danh sách thiết bị: ' . $e->getMessage());
        }
    }

    /**
     * Display thiết bị grouped by phòng
     */
    public function indexByPhong(Request $request)
    {
        try {
            $filters = $request->only(['search', 'loai_thiet_bi', 'trang_thai', 'can_bao_duong']);
            $groupedThietBis = $this->thietBiService->getGroupedByPhong($filters);
            $phongs = $this->phongService->getActivePhongs();

            return Inertia::render('ThietBi/IndexByPhong', [
                'groupedThietBis' => $groupedThietBis,
                'phongs' => $phongs,
                'filters' => $filters
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải danh sách thiết bị: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $phongs = $this->phongService->getActivePhongs();

            return Inertia::render('ThietBi/Create', [
                'phongs' => $phongs
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('thiet-bi.index')->with('error', 'Lỗi khi tải trang tạo mới: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreThietBiRequest $request)
    {
        try {
            $this->thietBiService->create($request->validated());
            return redirect()->route('thiet-bi.index')->with('success', 'Thêm thiết bị thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi thêm thiết bị: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $thietBi = $this->thietBiService->getById($id);
            $phongs = $this->phongService->getActivePhongs();

            return Inertia::render('ThietBi/Edit', [
                'thietBi' => $thietBi,
                'phongs' => $phongs
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('thiet-bi.index')->with('error', 'Không tìm thấy thiết bị: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateThietBiRequest $request, $id)
    {
        try {
            $this->thietBiService->update($id, $request->validated());
            return redirect()->route('thiet-bi.index')->with('success', 'Cập nhật thiết bị thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi cập nhật thiết bị: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->thietBiService->delete($id);
            return redirect()->route('thiet-bi.index')->with('success', 'Xóa thiết bị thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa thiết bị: ' . $e->getMessage());
        }
    }

    /**
     * Show form to duplicate an existing device
     */
    public function duplicate($id)
    {
        try {
            $thietBi = $this->thietBiService->getById($id);
            $phongs = $this->phongService->getActivePhongs();

            return Inertia::render('ThietBi/Duplicate', [
                'thietBi' => $thietBi,
                'phongs' => $phongs
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('thiet-bi.index')->with('error', 'Không tìm thấy thiết bị: ' . $e->getMessage());
        }
    }
}
