<?php

namespace App\Http\Controllers;

use App\Models\DotBaoCao;
use App\Services\BaoCaoBgdService;
use App\Exports\BaoCaoBgdExport;
use Illuminate\Http\Request;
use Inertia\Inertia;

class XuatBaoCaoController extends Controller
{
    protected $baoCaoService;

    public function __construct(BaoCaoBgdService $baoCaoService)
    {
        $this->baoCaoService = $baoCaoService;
    }

    /**
     * Hiển thị trang quản lý đợt báo cáo
     */
    public function index(Request $request)
    {
        $dotBaoCaos = DotBaoCao::with('nguoiTao')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($dot) {
                return [
                    'id' => $dot->id,
                    'ten_dot' => $dot->ten_dot,
                    'nam_hoc' => $dot->nam_hoc,
                    'mo_ta' => $dot->mo_ta,
                    'ngay_tong_hop' => $dot->ngay_tong_hop ? $dot->ngay_tong_hop->format('d/m/Y') : null,
                    'nguoi_tao' => $dot->nguoiTao ? $dot->nguoiTao->name : null,
                    'trang_thai' => $dot->trang_thai,
                    'created_at' => $dot->created_at->format('d/m/Y H:i'),
                ];
            });

        // Nếu có chọn đợt báo cáo để preview
        $previewData = null;
        $selectedId = $request->get('preview');
        if ($selectedId) {
            $dot = DotBaoCao::with([
                'bcLoaiPhongs',
                'bcTieuChuanCsvcs',
                'bcKhuonViens',
                'bcCongTrinhDaoTaos',
                'bcHaTangCntts',
            ])->find($selectedId);

            if ($dot) {
                $previewData = [
                    'id' => $dot->id,
                    'ten_dot' => $dot->ten_dot,
                    'bcLoaiPhongs' => $dot->bcLoaiPhongs,
                    'bcTieuChuanCsvcs' => $dot->bcTieuChuanCsvcs,
                    'bcKhuonViens' => $dot->bcKhuonViens,
                    'bcCongTrinhDaoTaos' => $dot->bcCongTrinhDaoTaos,
                    'bcHaTangCntts' => $dot->bcHaTangCntts,
                ];
            }
        }

        return Inertia::render('XuatBaoCao/Index', [
            'dotBaoCaos' => $dotBaoCaos,
            'previewData' => $previewData,
            'selectedId' => $selectedId ? (int) $selectedId : null,
        ]);
    }

    /**
     * Tạo đợt báo cáo mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_dot' => 'required|string|max:255',
            'nam_hoc' => 'nullable|string|max:20',
            'mo_ta' => 'nullable|string|max:1000',
        ]);

        $dotBaoCao = DotBaoCao::create(array_merge($validated, [
            'nguoi_tao_id' => auth()->id(),
            'trang_thai' => 'draft',
        ]));

        return back()->with('success', 'Đã tạo đợt báo cáo mới!');
    }

    /**
     * Xóa đợt báo cáo
     */
    public function destroy(DotBaoCao $dotBaoCao)
    {
        $dotBaoCao->delete();
        return back()->with('success', 'Đã xóa đợt báo cáo!');
    }

    /**
     * Tổng hợp dữ liệu cho đợt báo cáo
     */
    public function tongHop(DotBaoCao $dotBaoCao)
    {
        $this->baoCaoService->tongHopBaoCao($dotBaoCao);
        return back()->with('success', 'Đã tổng hợp dữ liệu báo cáo thành công!');
    }

    /**
     * Xem chi tiết/preview đợt báo cáo - redirect về index với preview
     */
    public function show(DotBaoCao $dotBaoCao)
    {
        return redirect()->route('xuat-bao-cao.index', ['preview' => $dotBaoCao->id]);
    }

    /**
     * Xuất file Excel
     */
    public function export(DotBaoCao $dotBaoCao, Request $request)
    {
        $loaiBaoCao = $request->get('loai', 'all');

        $dotBaoCao->load([
            'bcLoaiPhongs',
            'bcTieuChuanCsvcs',
            'bcKhuonViens',
            'bcCongTrinhDaoTaos',
            'bcHaTangCntts',
        ]);

        $export = new BaoCaoBgdExport($dotBaoCao, $loaiBaoCao);
        $filename = 'BaoCao_BGD_' . str_replace([' ', '/'], '_', $dotBaoCao->ten_dot) . '_' . date('Ymd') . '.xlsx';

        return $export->download($filename);
    }
}
