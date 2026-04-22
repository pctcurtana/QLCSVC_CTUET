<?php

namespace App\Http\Controllers;

use App\Services\BaoCaoSuCoService;
use App\Services\LichSuBaoDuongService;
use App\Http\Requests\BaoCaoSuCo\StoreBaoCaoSuCoRequest;
use App\Models\ThietBi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BaoCaoSuCoController extends Controller
{
    protected $baoCaoService;
    protected $lichSuService;

    public function __construct(
        BaoCaoSuCoService $baoCaoService,
        LichSuBaoDuongService $lichSuService
    ) {
        $this->baoCaoService = $baoCaoService;
        $this->lichSuService = $lichSuService;
    }

    // ─── Public: Room QR form ─────────────────────────────────────────────────

    public function showPhongForm(string $token)
    {
        $phong = $this->baoCaoService->getPhongByToken($token);

        if (!$phong) {
            abort(404, 'Phòng không tồn tại hoặc mã QR đã hết hiệu lực.');
        }

        return Inertia::render('BaoCao/PhongForm', [
            'phong' => $phong,
            'token' => $token,
        ]);
    }

    public function submitPhongForm(StoreBaoCaoSuCoRequest $request, string $token)
    {
        $phong = $this->baoCaoService->getPhongByToken($token);

        if (!$phong) {
            abort(404);
        }

        try {
            $this->baoCaoService->create(array_merge($request->validated(), [
                'phong_id'   => $phong->id,
                'ip_address' => $request->ip(),
                'trang_thai' => 'yeu_cau_sua_chua',
            ]));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['thiet_bi_id' => $e->getMessage()]);
        }

        return back()->with('success', 'Báo cáo đã được ghi nhận. Cảm ơn bạn!');
    }

    // ─── Auth: Device QR quick-repair form (token-based) ─────────────────────

    private function getThietBiByToken(string $token): ThietBi
    {
        $thietBi = ThietBi::where('qr_token', $token)
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->firstOrFail();
        return $thietBi->load('phong.khuNha.coSo');
    }

    public function showSuaChuaForm(string $token)
    {
        $thietBi = $this->getThietBiByToken($token);
        $soLanSuaChua = $thietBi->lichSuBaoDuongs()
            ->where('loai_bao_duong', 'sua_chua')
            ->count();

        return Inertia::render('BaoCao/SuaChuaForm', [
            'thietBi'      => $thietBi,
            'soLanSuaChua' => $soLanSuaChua,
            'token'        => $token,
        ]);
    }

    public function submitSuaChua(Request $request, string $token)
    {
        $thietBi = $this->getThietBiByToken($token);

        $validated = $request->validate([
            'hu_hong_mo_ta'  => 'required|string|min:5|max:500',
            'noi_dung'       => 'required|string|min:5|max:1000',
            'ngay_bao_duong' => 'required|date',
            'chi_phi'        => 'nullable|numeric|min:0',
            'trang_thai'     => 'in:hoan_thanh',
        ]);

        $nguoiThucHien = auth()->user()->name ?? auth()->user()->email;

        $this->lichSuService->create([
            'thiet_bi_id'     => $thietBi->id,
            'loai_bao_duong'  => 'sua_chua',
            'ngay_bao_duong'  => $validated['ngay_bao_duong'],
            'noi_dung'        => "Hư hỏng: {$validated['hu_hong_mo_ta']}\n\nSửa chữa: {$validated['noi_dung']}",
            'chi_phi'         => $validated['chi_phi'] ?? null,
            'nguoi_thuc_hien' => $nguoiThucHien,
            'trang_thai'      => 'hoan_thanh',
            'ghi_chu'         => 'Ghi nhận qua QR code',
        ]);

        // Tự động đóng tất cả báo cáo đang chờ của thiết bị này
        $this->baoCaoService->completeReportsForDevice($thietBi->id, $nguoiThucHien);

        return back()->with('success', 'Ghi nhận sửa chữa thành công!');
    }

    // ─── Admin: Incident report list ─────────────────────────────────────────

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'phong_id', 'muc_do', 'trang_thai']);
        $baoCaos = $this->baoCaoService->getAllPaginated($filters, 15);
        $stats   = $this->baoCaoService->getStats();

        return Inertia::render('BaoCaoSuCo/Index', [
            'baoCaos' => $baoCaos,
            'stats'   => $stats,
            'filters' => $filters,
        ]);
    }

    public function destroy(int $id)
    {
        $this->baoCaoService->delete($id);
        return back()->with('success', 'Đã xóa báo cáo!');
    }
}
