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
            ->withCount('lichSuBaoDuongs')
            ->where('trang_thai_du_lieu', 'hien_hanh');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('ma_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('ten_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('hang_san_xuat', 'like', "%{$search}%");
            });
        }

        if (isset($filters['phong_id']) && !empty($filters['phong_id'])) {
            $query->where('phong_id', $filters['phong_id']);
        }

        if (isset($filters['loai_thiet_bi']) && !empty($filters['loai_thiet_bi'])) {
            $query->where('loai_thiet_bi', $filters['loai_thiet_bi']);
        }

        if (isset($filters['trang_thai']) && !empty($filters['trang_thai'])) {
            $query->where('trang_thai', $filters['trang_thai']);
        }

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
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalQuantity(): int
    {
        return $this->model->where('trang_thai_du_lieu', 'hien_hanh')->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalValue(): float
    {
        return (float) $this->model->where('trang_thai_du_lieu', 'hien_hanh')->sum('gia_tri') ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getByPhong(int $phongId): Collection
    {
        return $this->model
            ->where('phong_id', $phongId)
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatsByType(): Collection
    {
        return $this->model
            ->selectRaw('loai_thiet_bi, COUNT(*) as so_luong, SUM(gia_tri) as gia_tri')
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->groupBy('loai_thiet_bi')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getNeedMaintenance(): Collection
    {
        return $this->model
            ->where('trang_thai_du_lieu', 'hien_hanh')
            ->whereNotNull('ngay_bao_duong_tiep_theo')
            ->whereDate('ngay_bao_duong_tiep_theo', '<=', now())
            ->with(['phong.khuNha.coSo'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getGroupedByPhong(array $filters = []): Collection
    {
        $query = $this->model->query()
            ->with(['phong.khuNha.coSo'])
            ->where('trang_thai_du_lieu', 'hien_hanh');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('ma_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('ten_thiet_bi', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('hang_san_xuat', 'like', "%{$search}%");
            });
        }

        if (isset($filters['loai_thiet_bi']) && !empty($filters['loai_thiet_bi'])) {
            $query->where('loai_thiet_bi', $filters['loai_thiet_bi']);
        }

        if (isset($filters['trang_thai']) && !empty($filters['trang_thai'])) {
            $query->where('trang_thai', $filters['trang_thai']);
        }

        if (isset($filters['can_bao_duong']) && $filters['can_bao_duong'] === 'true') {
            $query->whereNotNull('ngay_bao_duong_tiep_theo')
                  ->whereDate('ngay_bao_duong_tiep_theo', '<=', now());
        }

        return $query->get()->groupBy('phong_id');
    }
}

