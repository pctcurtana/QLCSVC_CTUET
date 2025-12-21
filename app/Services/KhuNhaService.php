<?php

namespace App\Services;

use App\Contracts\Repositories\KhuNhaRepositoryInterface;
use App\Contracts\Services\KhuNhaServiceInterface;
use App\Models\KhuNha;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class KhuNhaService
{
    /**
     * @var KhuNhaRepositoryInterface
     */
    protected $khuNhaRepository;

    /**
     * KhuNhaService constructor.
     *
     * @param KhuNhaRepositoryInterface $khuNhaRepository
     */
    public function __construct(KhuNhaRepositoryInterface $khuNhaRepository)
    {
        $this->khuNhaRepository = $khuNhaRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->khuNhaRepository->paginate($filters, $perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveKhuNhas(): Collection
    {
        return $this->khuNhaRepository->getActive(['id', 'ten_khu_nha', 'co_so_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int $id): KhuNha
    {
        $khuNha = $this->khuNhaRepository->find($id);
        
        if (!$khuNha) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Không tìm thấy khu nhà');
        }

        // Load relationship
        $khuNha->load('coSo');

        return $khuNha;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): KhuNha
    {
        // Tự động tính diện tích sàn sử dụng cho đào tạo
        $data['dien_tich_san_dao_tao'] = $this->calculateDienTichSanDaoTao(
            $data['tong_dien_tich_san'],
            $data['he_so_su_dung_dao_tao']
        );
        return $this->khuNhaRepository->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): KhuNha
    {
        // Tự động tính diện tích sàn sử dụng cho đào tạo
        $data['dien_tich_san_dao_tao'] = $this->calculateDienTichSanDaoTao(
            $data['tong_dien_tich_san'],
            $data['he_so_su_dung_dao_tao']
        );
        return $this->khuNhaRepository->update($id, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        return $this->khuNhaRepository->delete($id);
    }

    /**
     * Tính diện tích sàn sử dụng cho đào tạo
     * DT sàn đào tạo = Tổng DT sàn xây dựng × Hệ số sử dụng cho đào tạo
     * 
     * @param float $tongDienTichSan Tổng diện tích sàn xây dựng (m²)
     * @param float $heSoSuDung Hệ số DT sử dụng cho đào tạo (mặc định 0.7)
     * @return float Diện tích sàn sử dụng cho đào tạo
     */
    public function calculateDienTichSanDaoTao(float $tongDienTichSan, float $heSoSuDung): float
    {
        return $tongDienTichSan * $heSoSuDung;
    }
}

