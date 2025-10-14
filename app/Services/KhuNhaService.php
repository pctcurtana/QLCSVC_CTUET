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
        return $this->khuNhaRepository->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): KhuNha
    {
        return $this->khuNhaRepository->update($id, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        return $this->khuNhaRepository->delete($id);
    }
}

