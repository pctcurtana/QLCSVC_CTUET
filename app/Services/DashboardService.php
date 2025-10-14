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
            'tong_dien_tich' => $totalArea['tong_dien_tich'],
            'dien_tich_san_xay_dung' => $totalArea['dien_tich_san_xay_dung'],
            'dien_tich_con_lai' => $totalArea['dien_tich_con_lai'],
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
                'dien_tich' => $coSo->tong_dien_tich,
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

