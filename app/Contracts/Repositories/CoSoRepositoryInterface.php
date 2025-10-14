<?php

namespace App\Contracts\Repositories;

use App\Models\CoSo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CoSoRepositoryInterface
{
    /**
     * Lấy danh sách cơ sở có phân trang và filter
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy tất cả cơ sở
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Lấy cơ sở theo ID
     *
     * @param int $id
     * @return CoSo|null
     */
    public function find(int $id): ?CoSo;

    /**
     * Tạo cơ sở mới
     *
     * @param array $data
     * @return CoSo
     */
    public function create(array $data): CoSo;

    /**
     * Cập nhật cơ sở
     *
     * @param int $id
     * @param array $data
     * @return CoSo
     */
    public function update(int $id, array $data): CoSo;

    /**
     * Xóa cơ sở
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Lấy cơ sở đang hoạt động
     *
     * @param array $columns
     * @return Collection
     */
    public function getActive(array $columns = ['*']): Collection;

    /**
     * Đếm tổng số cơ sở
     *
     * @return int
     */
    public function count(): int;

    /**
     * Tính tổng diện tích các cơ sở
     *
     * @return array
     */
    public function getTotalArea(): array;
}

