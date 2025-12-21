<?php

namespace App\Repositories;

use App\Contracts\Repositories\ThietBiRepositoryInterface;
use App\Models\ThietBi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ThietBiRepository implements ThietBiRepositoryInterface
{
    /**
     * @var ThietBi
     */
    protected $model;

    /**
     * ThietBiRepository constructor.
     *
     * @param ThietBi $model
     */
    public function __construct(ThietBi $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['phong.khuNha.coSo'])
            ->withCount('lichSuBaoDuongs');

        // Filter by search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('ma_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('ten_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('hang_san_xuat', 'like', "%{$search}%");
            });
        }

        // Filter by phong_id
        if (isset($filters['phong_id']) && !empty($filters['phong_id'])) {
            $query->where('phong_id', $filters['phong_id']);
        }

        // Filter by loai_thiet_bi
        if (isset($filters['loai_thiet_bi']) && !empty($filters['loai_thiet_bi'])) {
            $query->where('loai_thiet_bi', $filters['loai_thiet_bi']);
        }

        // Filter by status
        if (isset($filters['trang_thai']) && !empty($filters['trang_thai'])) {
            $query->where('trang_thai', $filters['trang_thai']);
        }

        // Filter by can_bao_duong
        if (isset($filters['can_bao_duong']) && $filters['can_bao_duong'] === 'true') {
            $query->whereNotNull('ngay_bao_duong_tiep_theo')
                  ->whereDate('ngay_bao_duong_tiep_theo', '<=', now());
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
    public function find(int $id): ?ThietBi
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): ThietBi
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): ThietBi
    {
        $thietBi = $this->model->findOrFail($id);
        $thietBi->update($data);
        return $thietBi->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $thietBi = $this->model->findOrFail($id);
        return $thietBi->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getActive(array $columns = ['*']): Collection
    {
        return $this->model
            ->with('phong.khuNha')
            ->where('trang_thai', 'tot')
            ->get($columns);
    }

    /**
     * {@inheritDoc}
     * Đếm tổng số thiết bị (mỗi record = 1 máy)
     */
    public function getTotalQuantity(): int
    {
        return $this->model->count();
    }

    /**
     * {@inheritDoc}
     * Tính tổng giá trị thiết bị (không cần nhân số lượng nữa)
     */
    public function getTotalValue(): float
    {
        return (float) $this->model->sum('gia_tri') ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getByPhong(int $phongId): Collection
    {
        return $this->model
            ->where('phong_id', $phongId)
            ->get();
    }

    /**
     * {@inheritDoc}
     * Thống kê theo loại thiết bị (đếm số máy, không sum số lượng)
     */
    public function getStatsByType(): Collection
    {
        return $this->model
            ->selectRaw('loai_thiet_bi, COUNT(*) as so_luong, SUM(gia_tri) as gia_tri')
            ->groupBy('loai_thiet_bi')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getNeedMaintenance(): Collection
    {
        return $this->model
            ->whereNotNull('ngay_bao_duong_tiep_theo')
            ->whereDate('ngay_bao_duong_tiep_theo', '<=', now())
            ->with(['phong.khuNha.coSo'])
            ->get();
    }

    /**
     * Get all thiet bi grouped by phong
     * 
     * @param array $filters
     * @return Collection
     */
    public function getGroupedByPhong(array $filters = []): Collection
    {
        $query = $this->model->query()
            ->with(['phong.khuNha.coSo']);

        // Filter by search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('ma_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('ten_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('hang_san_xuat', 'like', "%{$search}%");
            });
        }

        // Filter by loai_thiet_bi
        if (isset($filters['loai_thiet_bi']) && !empty($filters['loai_thiet_bi'])) {
            $query->where('loai_thiet_bi', $filters['loai_thiet_bi']);
        }

        // Filter by status
        if (isset($filters['trang_thai']) && !empty($filters['trang_thai'])) {
            $query->where('trang_thai', $filters['trang_thai']);
        }

        // Filter by can_bao_duong
        if (isset($filters['can_bao_duong']) && $filters['can_bao_duong'] === 'true') {
            $query->whereNotNull('ngay_bao_duong_tiep_theo')
                  ->whereDate('ngay_bao_duong_tiep_theo', '<=', now());
        }

        // Get all thiet bi and group by phong_id
        $thietBis = $query->get();
        
        // Group by phong
        return $thietBis->groupBy('phong_id');
    }
}

