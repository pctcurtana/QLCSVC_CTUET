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
            ->select('khu_nhas.*')
            ->join('co_sos', 'khu_nhas.co_so_id', '=', 'co_sos.id')
            ->with('coSo')
            ->withCount('phongs')
            ->where('khu_nhas.trang_thai_du_lieu', 'hien_hanh');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('khu_nhas.ma_khu_nha', 'like', "%{$search}%")
                  ->orWhere('khu_nhas.ten_khu_nha', 'like', "%{$search}%");
            });
        }

        if (isset($filters['co_so_id']) && !empty($filters['co_so_id'])) {
            $query->where('khu_nhas.co_so_id', $filters['co_so_id']);
        }

        if (isset($filters['loai_khu_nha']) && !empty($filters['loai_khu_nha'])) {
            $query->where('khu_nhas.loai_khu_nha', $filters['loai_khu_nha']);
        }

        return $query->orderBy('co_sos.ten_co_so', 'asc')
            ->orderBy('khu_nhas.ten_khu_nha', 'asc')
            ->paginate($perPage);
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
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->orderBy('ten_khu_nha', 'asc')
            ->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->model->where('trang_thai_du_lieu', 'hien_hanh')->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getByCoSo(int $coSoId, array $columns = ['id', 'ten_khu_nha', 'ma_khu_nha', 'co_so_id']): Collection
    {
        return $this->model
            ->where('co_so_id', $coSoId)
            ->where('trang_thai', 'active')
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->orderBy('ten_khu_nha', 'asc')
            ->get($columns);
    }
}

