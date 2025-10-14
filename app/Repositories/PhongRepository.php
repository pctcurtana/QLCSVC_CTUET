<?php

namespace App\Repositories;

use App\Contracts\Repositories\PhongRepositoryInterface;
use App\Models\Phong;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PhongRepository implements PhongRepositoryInterface
{
    /**
     * @var Phong
     */
    protected $model;

    /**
     * PhongRepository constructor.
     *
     * @param Phong $model
     */
    public function __construct(Phong $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['khuNha.coSo'])
            ->withCount('thietBis');

        // Filter by search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('ma_phong', 'like', "%{$search}%")
                  ->orWhere('ten_phong', 'like', "%{$search}%");
            });
        }

        // Filter by khu_nha_id
        if (isset($filters['khu_nha_id']) && !empty($filters['khu_nha_id'])) {
            $query->where('khu_nha_id', $filters['khu_nha_id']);
        }

        // Filter by loai_phong
        if (isset($filters['loai_phong']) && !empty($filters['loai_phong'])) {
            $query->where('loai_phong', $filters['loai_phong']);
        }

        // Filter by status
        if (isset($filters['trang_thai']) && !empty($filters['trang_thai'])) {
            $query->where('trang_thai', $filters['trang_thai']);
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
    public function find(int $id): ?Phong
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Phong
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): Phong
    {
        $phong = $this->model->findOrFail($id);
        $phong->update($data);
        return $phong->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $phong = $this->model->findOrFail($id);
        return $phong->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getActive(array $columns = ['*']): Collection
    {
        return $this->model
            ->with('khuNha.coSo')
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
    public function getByKhuNha(int $khuNhaId): Collection
    {
        return $this->model
            ->where('khu_nha_id', $khuNhaId)
            ->where('trang_thai', 'active')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatsByType(): Collection
    {
        return $this->model
            ->selectRaw('loai_phong, COUNT(*) as so_luong, SUM(dien_tich) as dien_tich')
            ->groupBy('loai_phong')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatsByStatus(): Collection
    {
        return $this->model
            ->selectRaw('trang_thai, COUNT(*) as so_luong')
            ->groupBy('trang_thai')
            ->get();
    }
}

