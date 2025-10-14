<?php

namespace App\Contracts\Repositories;

use App\Models\ThietBi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ThietBiRepositoryInterface
{
    /**
     * Lấy danh sách thiết bị có phân trang và filter
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy tất cả thiết bị
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Lấy thiết bị theo ID
     *
     * @param int $id
     * @return ThietBi|null
     */
    public function find(int $id): ?ThietBi;

    /**
     * Tạo thiết bị mới
     *
     * @param array $data
     * @return ThietBi
     */
    public function create(array $data): ThietBi;

    /**
     * Cập nhật thiết bị
     *
     * @param int $id
     * @param array $data
     * @return ThietBi
     */
    public function update(int $id, array $data): ThietBi;

    /**
     * Xóa thiết bị
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Lấy thiết bị đang hoạt động với thông tin phòng
     *
     * @param array $columns
     * @return Collection
     */
    public function getActive(array $columns = ['*']): Collection;

    /**
     * Đếm tổng số lượng thiết bị
     *
     * @return int
     */
    public function getTotalQuantity(): int;

    /**
     * Tính tổng giá trị thiết bị
     *
     * @return float
     */
    public function getTotalValue(): float;

    /**
     * Lấy thiết bị theo phòng
     *
     * @param int $phongId
     * @return Collection
     */
    public function getByPhong(int $phongId): Collection;

    /**
     * Lấy thống kê theo loại thiết bị
     *
     * @return Collection
     */
    public function getStatsByType(): Collection;

    /**
     * Lấy thiết bị cần bảo dưỡng
     *
     * @return Collection
     */
    public function getNeedMaintenance(): Collection;
}

