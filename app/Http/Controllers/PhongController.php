<?php

namespace App\Http\Controllers;

use App\Services\KhuNhaService;
use App\Services\PhongService;
use App\Http\Requests\Phong\StorePhongRequest;
use App\Http\Requests\Phong\UpdatePhongRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhongController extends Controller
{
    /**
     * @var PhongService
     */
    protected $phongService;

    /**
     * @var KhuNhaService
     */
    protected $khuNhaService;

    /**
     * PhongController constructor.
     *
     * @param PhongServiceInterface $phongService
     * @param KhuNhaServiceInterface $khuNhaService
     */
    public function __construct(
        PhongService $phongService,
        KhuNhaService $khuNhaService
    ) {
        $this->phongService = $phongService;
        $this->khuNhaService = $khuNhaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'khu_nha_id', 'loai_phong', 'trang_thai']);
            $phongs = $this->phongService->getAllPaginated($filters, 10);
            $khuNhas = $this->khuNhaService->getActiveKhuNhas();

            return Inertia::render('Phong/Index', [
                'phongs' => $phongs,
                'khuNhas' => $khuNhas,
                'filters' => $filters
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải danh sách phòng: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $khuNhas = $this->khuNhaService->getActiveKhuNhas();

            return Inertia::render('Phong/Create', [
                'khuNhas' => $khuNhas
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('phong.index')->with('error', 'Lỗi khi tải trang tạo mới: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhongRequest $request)
    {
        try {
            $this->phongService->create($request->validated());
            return redirect()->route('phong.index')->with('success', 'Thêm phòng thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi thêm phòng: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $phong = $this->phongService->getById($id);
            $khuNhas = $this->khuNhaService->getActiveKhuNhas();

            return Inertia::render('Phong/Edit', [
                'phong' => $phong,
                'khuNhas' => $khuNhas
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('phong.index')->with('error', 'Không tìm thấy phòng: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePhongRequest $request, $id)
    {
        try {
            $this->phongService->update($id, $request->validated());
            return redirect()->route('phong.index')->with('success', 'Cập nhật phòng thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi cập nhật phòng: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->phongService->delete($id);
            return redirect()->route('phong.index')->with('success', 'Xóa phòng thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa phòng: ' . $e->getMessage());
        }
    }
}
