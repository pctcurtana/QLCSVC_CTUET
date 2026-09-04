<?php

namespace App\Contracts\Repositories;

use App\Models\DotKiemTraThietBi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DotKiemTraThietBiRepositoryInterface
{
    /**
     * Lấy danh sách đợt kiểm tra có phân trang và filter
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy đợt kiểm tra theo ID
     *
     * @param int $id
     * @return DotKiemTraThietBi|null
     */
    public function find(int $id): ?DotKiemTraThietBi;

    /**
     * Tạo đợt kiểm tra mới
     *
     * @param array $data
     * @return DotKiemTraThietBi
     */
    public function create(array $data): DotKiemTraThietBi;

    /**
     * Cập nhật đợt kiểm tra
     *
     * @param int $id
     * @param array $data
     * @return DotKiemTraThietBi
     */
    public function update(int $id, array $data): DotKiemTraThietBi;

    /**
     * Xóa đợt kiểm tra
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Thống kê tổng quan đợt kiểm tra
     *
     * @return array
     */
    public function getStats(): array;

    /**
     * Lấy đợt kiểm tra đang active
     *
     * @return DotKiemTraThietBi|null
     */
    public function getActiveDot(): ?DotKiemTraThietBi;

    /**
     * Tắt tất cả đợt kiểm tra (deactivate all)
     *
     * @return void
     */
    public function deactivateAll(): void;

    /**
     * Lấy danh sách đợt kiểm tra cho dropdown
     *
     * @return Collection
     */
    public function getAllForDropdown(): Collection;
}
