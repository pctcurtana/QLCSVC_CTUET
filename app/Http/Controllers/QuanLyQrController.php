<?php

namespace App\Http\Controllers;

use App\Services\PhongService;
use App\Services\ThietBiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class QuanLyQrController extends Controller
{
    protected $phongService;
    protected $thietBiService;

    public function __construct(PhongService $phongService, ThietBiService $thietBiService)
    {
        $this->phongService   = $phongService;
        $this->thietBiService = $thietBiService;
    }

    public function index()
    {
        $phongs = \DB::table('phongs as p')
            ->where('p.trang_thai_du_lieu', 'hien_hanh')
            ->leftJoin('khu_nhas as kn', 'kn.id', '=', 'p.khu_nha_id')
            ->leftJoin('co_sos as cs', 'cs.id', '=', 'kn.co_so_id')
            ->select(
                'p.id', 'p.ma_phong', 'p.ten_phong', 'p.qr_token',
                'p.khu_nha_id', 'kn.co_so_id',
                'kn.ten_khu_nha', 'cs.ten_co_so'
            )
            ->orderBy('cs.ten_co_so')->orderBy('kn.ten_khu_nha')->orderBy('p.ten_phong')
            ->get();

        $thietBis = \DB::table('thiet_bis as tb')
            ->where('tb.trang_thai_du_lieu', 'hien_hanh')
            ->leftJoin('phongs as p', 'p.id', '=', 'tb.phong_id')
            ->leftJoin('khu_nhas as kn', 'kn.id', '=', 'p.khu_nha_id')
            ->leftJoin('co_sos as cs', 'cs.id', '=', 'kn.co_so_id')
            ->select(
                'tb.id', 'tb.qr_token', 'tb.ma_thiet_bi', 'tb.ten_thiet_bi', 'tb.loai_thiet_bi',
                'tb.phong_id', 'p.khu_nha_id', 'kn.co_so_id',
                'p.ten_phong', 'kn.ten_khu_nha', 'cs.ten_co_so'
            )
            ->orderBy('cs.ten_co_so')->orderBy('kn.ten_khu_nha')->orderBy('p.ten_phong')
            ->get();

        // Danh sách cơ sở, khu nhà, phòng cho bộ lọc
        $coSos = \DB::table('co_sos')
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->select('id', 'ten_co_so')
            ->orderBy('ten_co_so')
            ->get();

        $khuNhas = \DB::table('khu_nhas')
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->select('id', 'ten_khu_nha', 'co_so_id')
            ->orderBy('ten_khu_nha')
            ->get();

        $phongsList = \DB::table('phongs')
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->select('id', 'ten_phong', 'khu_nha_id')
            ->orderBy('ten_phong')
            ->get();

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
        \DB::table('phongs')->where('id', $phong_id)->update([
            'qr_token'   => Str::uuid(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Đã tạo lại mã QR cho phòng!');
    }

    public function regenerateThietBiQr(int $thiet_bi_id)
    {
        \DB::table('thiet_bis')
            ->where('id', $thiet_bi_id)
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->update([
                'qr_token'   => Str::uuid(),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Đã tạo lại mã QR cho thiết bị!');
    }
}
