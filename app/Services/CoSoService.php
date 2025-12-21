<?php

namespace App\Services;

use App\Contracts\Repositories\CoSoRepositoryInterface;
use App\Contracts\Services\CoSoServiceInterface;
use App\Models\CoSo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CoSoService
{
    /**
     * @var CoSoRepositoryInterface
     */
    protected $coSoRepository;

    /**
     * CoSoService constructor.
     *
     * @param CoSoRepositoryInterface $coSoRepository
     */
    public function __construct(CoSoRepositoryInterface $coSoRepository)
    {
        $this->coSoRepository = $coSoRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->coSoRepository->paginate($filters, $perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveCoSos(): Collection
    {
        return $this->coSoRepository->getActive(['id', 'ten_co_so']);
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int $id): CoSo
    {
        $coSo = $this->coSoRepository->find($id);
        if (!$coSo) {
            throw new \App\Exceptions\ResourceNotFoundException('Không tìm thấy cơ sở với ID: ' . $id);
        }
        return $coSo;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): CoSo
    {
        // Tự động tính diện tích quy đổi = diện tích đất * vị trí khuôn viên
        $data['dien_tich_quy_doi'] = $this->calculateDienTichQuyDoi(
            $data['dien_tich_dat'],
            $data['vi_tri_khuon_vien']
        );
        return $this->coSoRepository->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): CoSo
    {
        $this->getById($id);
        // Tự động tính diện tích quy đổi = diện tích đất * vị trí khuôn viên
        $data['dien_tich_quy_doi'] = $this->calculateDienTichQuyDoi(
            $data['dien_tich_dat'],
            $data['vi_tri_khuon_vien']
        );
        return $this->coSoRepository->update($id, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $this->getById($id);
        return $this->coSoRepository->delete($id);
    }

    /**
     * Tính diện tích quy đổi theo công thức BGD
     * Diện tích quy đổi = Diện tích đất × Vị trí khuôn viên
     * 
     * @param float $dienTichDat Diện tích đất (m²)
     * @param float $viTriKhuonVien Hệ số vị trí khuôn viên (mặc định 2.5 theo BGD)
     * @return float Diện tích quy đổi
     */
    public function calculateDienTichQuyDoi(float $dienTichDat, float $viTriKhuonVien): float
    {
        return $dienTichDat * $viTriKhuonVien;
    }
}

