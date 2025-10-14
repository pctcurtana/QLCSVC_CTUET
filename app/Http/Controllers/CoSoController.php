<?php

namespace App\Http\Controllers;

use App\Services\CoSoService;
use App\Http\Requests\CoSo\StoreCoSoRequest;
use App\Http\Requests\CoSo\UpdateCoSoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoSoController extends Controller
{
    /**
     * @var CoSoService
     */
    protected $coSoService;

    /**
     * CoSoController constructor.
     *
     * @param CoSoService $coSoService
     */
    public function __construct(CoSoService $coSoService)
    {
        $this->coSoService = $coSoService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'trang_thai']);
            $coSos = $this->coSoService->getAllPaginated($filters, 10);

            return Inertia::render('CoSo/Index', [
                'coSos' => $coSos,
                'filters' => $filters
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải danh sách cơ sở: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('CoSo/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoSoRequest $request)
    {
        try {
            $this->coSoService->create($request->validated());
            return redirect()->route('co-so.index')->with('success', 'Thêm cơ sở thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi thêm cơ sở: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $coSo = $this->coSoService->getById($id);

            return Inertia::render('CoSo/Edit', [
                'coSo' => $coSo
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('co-so.index')->with('error', 'Không tìm thấy cơ sở: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoSoRequest $request, $id)
    {
        try {
            $this->coSoService->update($id, $request->validated());
            return redirect()->route('co-so.index')->with('success', 'Cập nhật cơ sở thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi cập nhật cơ sở: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->coSoService->delete($id);
            return redirect()->route('co-so.index')->with('success', 'Xóa cơ sở thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa cơ sở: ' . $e->getMessage());
        }
    }
}

