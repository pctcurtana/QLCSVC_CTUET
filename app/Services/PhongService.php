<?php

namespace App\Services;

use App\Contracts\Repositories\PhongRepositoryInterface;
use App\Contracts\Services\PhongServiceInterface;
use App\Models\Phong;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PhongService
{
    /**
     * @var PhongRepositoryInterface
     */
    protected $phongRepository;

    /**
     * PhongService constructor.
     *
     * @param PhongRepositoryInterface $phongRepository
     */
    public function __construct(PhongRepositoryInterface $phongRepository)
    {
        $this->phongRepository = $phongRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->phongRepository->paginate($filters, $perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivePhongs(): Collection
    {
        return $this->phongRepository->getActive(['id', 'ten_phong', 'khu_nha_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int $id): Phong
    {
        $phong = $this->phongRepository->find($id);
        
        if (!$phong) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Không tìm thấy phòng');
        }

        // Load relationships
        $phong->load('khuNha.coSo');

        return $phong;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Phong
    {
        return $this->phongRepository->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): Phong
    {
        return $this->phongRepository->update($id, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        return $this->phongRepository->delete($id);
    }
}

