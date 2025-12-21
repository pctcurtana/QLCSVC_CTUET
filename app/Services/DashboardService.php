<?php

namespace App\Services;

use App\Contracts\Repositories\CoSoRepositoryInterface;
use App\Contracts\Repositories\KhuNhaRepositoryInterface;
use App\Contracts\Repositories\PhongRepositoryInterface;
use App\Contracts\Repositories\ThietBiRepositoryInterface;
use App\Models\CoSo;

class DashboardService
{
    /**
     * @var CoSoRepositoryInterface
     */
    protected $coSoRepository;

    /**
     * @var KhuNhaRepositoryInterface
     */
    protected $khuNhaRepository;

    /**
     * @var PhongRepositoryInterface
     */
    protected $phongRepository;

    /**
     * @var ThietBiRepositoryInterface
     */
    protected $thietBiRepository;

    /**
     * DashboardService constructor.
     */
    public function __construct(
        CoSoRepositoryInterface $coSoRepository,
        KhuNhaRepositoryInterface $khuNhaRepository,
        PhongRepositoryInterface $phongRepository,
        ThietBiRepositoryInterface $thietBiRepository
    ) {
        $this->coSoRepository = $coSoRepository;
        $this->khuNhaRepository = $khuNhaRepository;
        $this->phongRepository = $phongRepository;
        $this->thietBiRepository = $thietBiRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getDashboardData(): array
    {
        return [
            'statistics' => $this->getOverviewStatistics(),
            'thongKeLoaiPhong' => $this->getRoomTypeStatistics(),
            'thongKeLoaiThietBi' => $this->getEquipmentTypeStatistics(),
            'thongKeCoSo' => $this->getFacilityStatistics(),
            'thongKeTrangThaiPhong' => $this->getRoomStatusStatistics(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getOverviewStatistics(): array
    {
        $totalArea = $this->coSoRepository->getTotalArea();

        return [
            'tong_co_so' => $this->coSoRepository->count(),
            'tong_khu_nha' => $this->khuNhaRepository->count(),
            'tong_phong' => $this->phongRepository->count(),
            'tong_thiet_bi' => $this->thietBiRepository->getTotalQuantity(),
            'tong_gia_tri_thiet_bi' => $this->thietBiRepository->getTotalValue(),
            // Cấu trúc mới: dien_tich_dat, vi_tri_khuon_vien, dien_tich_quy_doi
            'dien_tich_dat' => $totalArea['dien_tich_dat'],
            'vi_tri_khuon_vien_tb' => $totalArea['vi_tri_khuon_vien_tb'],
            'dien_tich_quy_doi' => $totalArea['dien_tich_quy_doi'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRoomTypeStatistics()
    {
        return $this->phongRepository->getStatsByType();
    }

    /**
     * {@inheritDoc}
     */
    public function getEquipmentTypeStatistics()
    {
        return $this->thietBiRepository->getStatsByType();
    }

    /**
     * {@inheritDoc}
     */
    public function getFacilityStatistics()
    {
        return CoSo::with('khuNhas')->get()->map(function($coSo) {
            return [
                'ten_co_so' => $coSo->ten_co_so,
                'so_khu_nha' => $coSo->khuNhas->count(),
                'dien_tich' => $coSo->dien_tich_dat, // Đổi từ tong_dien_tich sang dien_tich_dat
            ];
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getRoomStatusStatistics()
    {
        return $this->phongRepository->getStatsByStatus();
    }
}

