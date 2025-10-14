<?php

namespace App\Repositories;

use App\Contracts\Repositories\LichSuBaoDuongRepositoryInterface;
use App\Models\LichSuBaoDuong;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LichSuBaoDuongRepository implements LichSuBaoDuongRepositoryInterface
{
    /**
     * @var LichSuBaoDuong
     */
    protected $model;

    /**
     * LichSuBaoDuongRepository constructor.
     *
     * @param LichSuBaoDuong $model
     */
    public function __construct(LichSuBaoDuong $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['thietBi.phong.khuNha.coSo']);

        // Filter by thiet_bi_id
        if (isset($filters['thiet_bi_id']) && !empty($filters['thiet_bi_id'])) {
            $query->where('thiet_bi_id', $filters['thiet_bi_id']);
        }

        // Filter by loai_bao_duong
        if (isset($filters['loai_bao_duong']) && !empty($filters['loai_bao_duong'])) {
            $query->where('loai_bao_duong', $filters['loai_bao_duong']);
        }

        // Filter by status
        if (isset($filters['trang_thai']) && !empty($filters['trang_thai'])) {
            $query->where('trang_thai', $filters['trang_thai']);
        }

        return $query->latest('ngay_bao_duong')->paginate($perPage);
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
    public function find(int $id): ?LichSuBaoDuong
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): LichSuBaoDuong
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): LichSuBaoDuong
    {
        $lichSu = $this->model->findOrFail($id);
        $lichSu->update($data);
        return $lichSu->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $lichSu = $this->model->findOrFail($id);
        return $lichSu->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getByThietBi(int $thietBiId): Collection
    {
        return $this->model
            ->where('thiet_bi_id', $thietBiId)
            ->latest('ngay_bao_duong')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getLatestByThietBi(int $thietBiId): ?LichSuBaoDuong
    {
        return $this->model
            ->where('thiet_bi_id', $thietBiId)
            ->latest('ngay_bao_duong')
            ->first();
    }
}

