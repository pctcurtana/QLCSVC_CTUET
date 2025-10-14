<?php

namespace App\Contracts\Repositories;

use App\Models\KhuNha;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface KhuNhaRepositoryInterface
{
    /**
     * Lấy danh sách khu nhà có phân trang và filter
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy tất cả khu nhà
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Lấy khu nhà theo ID
     *
     * @param int $id
     * @return KhuNha|null
     */
    public function find(int $id): ?KhuNha;

    /**
     * Tạo khu nhà mới
     *
     * @param array $data
     * @return KhuNha
     */
    public function create(array $data): KhuNha;

    /**
     * Cập nhật khu nhà
     *
     * @param int $id
     * @param array $data
     * @return KhuNha
     */
    public function update(int $id, array $data): KhuNha;

    /**
     * Xóa khu nhà
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Lấy khu nhà đang hoạt động với thông tin cơ sở
     *
     * @param array $columns
     * @return Collection
     */
    public function getActive(array $columns = ['*']): Collection;

    /**
     * Đếm tổng số khu nhà
     *
     * @return int
     */
    public function count(): int;

    /**
     * Lấy khu nhà theo cơ sở
     *
     * @param int $coSoId
     * @return Collection
     */
    public function getByCoSo(int $coSoId): Collection;
}

