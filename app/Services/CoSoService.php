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
        $data['dien_tich_con_lai'] = $this->calculateRemainingArea(
            $data['tong_dien_tich'],
            $data['dien_tich_san_xay_dung']
        );
        return $this->coSoRepository->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): CoSo
    {
        $this->getById($id);
        $data['dien_tich_con_lai'] = $this->calculateRemainingArea(
            $data['tong_dien_tich'],
            $data['dien_tich_san_xay_dung']
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
     * {@inheritDoc}
     */
    public function calculateRemainingArea(float $tongDienTich, float $dienTichSanXayDung): float
    {
        return $tongDienTich - $dienTichSanXayDung;
    }
}

