<?php

namespace App\Http\Controllers;

use App\Services\CoSoService;
use App\Services\KhuNhaService;
use App\Services\PhongService;
use App\Services\ThietBiService;
use Inertia\Inertia;

class QuanLyQrController extends Controller
{
    protected $phongService;
    protected $thietBiService;
    protected $coSoService;
    protected $khuNhaService;

    public function __construct(
        PhongService $phongService,
        ThietBiService $thietBiService,
        CoSoService $coSoService,
        KhuNhaService $khuNhaService
    ) {
        $this->phongService   = $phongService;
        $this->thietBiService = $thietBiService;
        $this->coSoService    = $coSoService;
        $this->khuNhaService  = $khuNhaService;
    }

    public function index()
    {
        $phongs   = $this->phongService->getForQrManagement();
        $thietBis = $this->thietBiService->getForQrManagement();

        // Danh sách cơ sở, khu nhà, phòng cho bộ lọc
        $coSos     = $this->coSoService->getActiveCoSos();
        $khuNhas   = $this->khuNhaService->getActiveKhuNhas();
        $phongsList = $this->phongService->getActivePhongs();

        $baseUrl = rtrim(config('app.url'), '/');

        return Inertia::render('QuanLyQr/Index', [
            'phongs'     => $phongs,
            'thietBis'   => $thietBis,
            'coSos'      => $coSos,
            'khuNhas'    => $khuNhas,
            'phongsList' => $phongsList,
            'baseUrl'    => $baseUrl,
        ]);
    }

    public function regeneratePhongQr(int $phong_id)
    {
        $this->phongService->regenerateQrToken($phong_id);

        return back()->with('success', 'Đã tạo lại mã QR cho phòng!');
    }

    public function regenerateThietBiQr(int $thiet_bi_id)
    {
        $this->thietBiService->regenerateQrToken($thiet_bi_id);

        return back()->with('success', 'Đã tạo lại mã QR cho thiết bị!');
    }
}
