<?php

namespace App\Repositories;

use App\Contracts\Repositories\DotKiemTraThietBiRepositoryInterface;
use App\Models\DotKiemTraThietBi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DotKiemTraThietBiRepository implements DotKiemTraThietBiRepositoryInterface
{
    /**
     * @var DotKiemTraThietBi
     */
    protected $model;

    public function __construct(DotKiemTraThietBi $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query()->latest('id');

        if (!empty($filters['search'])) {
            $query->where('ten_dot', 'like', '%' . $filters['search'] . '%');
        }

        if (($filters['trang_thai'] ?? '') === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['trang_thai'] ?? '') === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?DotKiemTraThietBi
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): DotKiemTraThietBi
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): DotKiemTraThietBi
    {
        $dot = $this->model->findOrFail($id);
        $dot->update($data);
        return $dot->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $dot = $this->model->findOrFail($id);
        return $dot->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getStats(): array
    {
        return [
            'tong'         => $this->model->count(),
            'dang_active'  => $this->model->where('is_active', true)->count(),
            'chua_active'  => $this->model->where('is_active', false)->count(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveDot(): ?DotKiemTraThietBi
    {
        return $this->model->where('is_active', true)->latest('id')->first();
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAll(): void
    {
        $this->model->query()->update(['is_active' => false]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllForDropdown(): Collection
    {
        return $this->model->orderByDesc('id')
            ->get(['id', 'ten_dot', 'ngay_bat_dau', 'ngay_ket_thuc']);
    }
}
