<?php

namespace App\Repositories;

use App\Contracts\Repositories\KhuNhaRepositoryInterface;
use App\Models\KhuNha;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class KhuNhaRepository implements KhuNhaRepositoryInterface
{
    /**
     * @var KhuNha
     */
    protected $model;

    /**
     * KhuNhaRepository constructor.
     *
     * @param KhuNha $model
     */
    public function __construct(KhuNha $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with('coSo')
            ->withCount('phongs');

        // Filter by search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('ma_khu_nha', 'like', "%{$search}%")
                  ->orWhere('ten_khu_nha', 'like', "%{$search}%");
            });
        }

        // Filter by co_so_id
        if (isset($filters['co_so_id']) && !empty($filters['co_so_id'])) {
            $query->where('co_so_id', $filters['co_so_id']);
        }

        // Filter by loai_khu_nha
        if (isset($filters['loai_khu_nha']) && !empty($filters['loai_khu_nha'])) {
            $query->where('loai_khu_nha', $filters['loai_khu_nha']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?KhuNha
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): KhuNha
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): KhuNha
    {
        $khuNha = $this->model->findOrFail($id);
        $khuNha->update($data);
        return $khuNha->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $khuNha = $this->model->findOrFail($id);
        return $khuNha->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getActive(array $columns = ['*']): Collection
    {
        return $this->model
            ->with('coSo')
            ->where('trang_thai', 'active')
            ->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getByCoSo(int $coSoId): Collection
    {
        return $this->model
            ->where('co_so_id', $coSoId)
            ->where('trang_thai', 'active')
            ->get();
    }
}

