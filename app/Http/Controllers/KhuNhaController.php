<?php

namespace App\Http\Controllers;

use App\Services\CoSoService;
use App\Services\KhuNhaService;
use App\Http\Requests\KhuNha\StoreKhuNhaRequest;
use App\Http\Requests\KhuNha\UpdateKhuNhaRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KhuNhaController extends Controller
{
    /**
     * @var KhuNhaService
     */
    protected $khuNhaService;

    /**
     * @var CoSoService
     */
    protected $coSoService;

    /**
     * KhuNhaController constructor.
     *
     * @param KhuNhaServiceInterface $khuNhaService
     * @param CoSoServiceInterface $coSoService
     */
    public function __construct(
        KhuNhaService $khuNhaService,
        CoSoService $coSoService
    ) {
        $this->khuNhaService = $khuNhaService;
        $this->coSoService = $coSoService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'co_so_id', 'loai_khu_nha']);
            $khuNhas = $this->khuNhaService->getAllPaginated($filters, 10);
            $coSos = $this->coSoService->getActiveCoSos();

            return Inertia::render('KhuNha/Index', [
                'khuNhas' => $khuNhas,
                'coSos' => $coSos,
                'filters' => $filters
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải danh sách khu nhà: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $coSos = $this->coSoService->getActiveCoSos();

            return Inertia::render('KhuNha/Create', [
                'coSos' => $coSos
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('khu-nha.index')->with('error', 'Lỗi khi tải trang tạo mới: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKhuNhaRequest $request)
    {
        try {
            $this->khuNhaService->create($request->validated());
            return redirect()->route('khu-nha.index')->with('success', 'Thêm khu nhà thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi thêm khu nhà: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $khuNha = $this->khuNhaService->getById($id);
            $coSos = $this->coSoService->getActiveCoSos();

            return Inertia::render('KhuNha/Edit', [
                'khuNha' => $khuNha,
                'coSos' => $coSos
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('khu-nha.index')->with('error', 'Không tìm thấy khu nhà: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKhuNhaRequest $request, $id)
    {
        try {
            $this->khuNhaService->update($id, $request->validated());
            return redirect()->route('khu-nha.index')->with('success', 'Cập nhật khu nhà thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi cập nhật khu nhà: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->khuNhaService->delete($id);
            return redirect()->route('khu-nha.index')->with('success', 'Xóa khu nhà thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa khu nhà: ' . $e->getMessage());
        }
    }
}
