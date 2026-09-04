<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Lấy danh sách người dùng có phân trang và filter
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy người dùng theo ID
     *
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User;

    /**
     * Tạo người dùng mới
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Cập nhật người dùng
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function update(int $id, array $data): User;

    /**
     * Xóa người dùng
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Đếm số lượng người dùng theo role
     *
     * @param string $role
     * @return int
     */
    public function countByRole(string $role): int;
}
