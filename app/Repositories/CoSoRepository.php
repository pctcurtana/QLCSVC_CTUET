<?php

namespace App\Repositories;

use App\Contracts\Repositories\CoSoRepositoryInterface;
use App\Models\CoSo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CoSoRepository implements CoSoRepositoryInterface
{
    /**
     * @var CoSo
     */
    protected $model;

    /**
     * CoSoRepository constructor.
     *
     * @param CoSo $model
     */
    public function __construct(CoSo $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query()->withCount('khuNhas');

        // Filter by search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('ma_co_so', 'like', "%{$search}%")
                  ->orWhere('ten_co_so', 'like', "%{$search}%")
                  ->orWhere('dia_chi', 'like', "%{$search}%");
            });
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
    public function find(int $id): ?CoSo
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): CoSo
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): CoSo
    {
        $coSo = $this->model->findOrFail($id);
        $coSo->update($data);
        return $coSo->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $coSo = $this->model->findOrFail($id);
        return $coSo->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getActive(array $columns = ['*']): Collection
    {
        return $this->model
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
     * Cập nhật theo cấu trúc mới: dien_tich_dat, vi_tri_khuon_vien, dien_tich_quy_doi
     */
    public function getTotalArea(): array
    {
        return [
            'dien_tich_dat' => $this->model->sum('dien_tich_dat'),
            'vi_tri_khuon_vien_tb' => $this->model->avg('vi_tri_khuon_vien'),
            'dien_tich_quy_doi' => $this->model->sum('dien_tich_quy_doi'),
        ];
    }
}

