<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    /**
     * Lấy tất cả quyền của một user
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Lấy quyền của user theo screen
     *
     * @param int $userId
     * @param int $screenId
     * @return UserPermission|null
     */
    public function getByUserAndScreen(int $userId, int $screenId): ?UserPermission;

    /**
     * Tạo quyền mới
     *
     * @param array $data
     * @return UserPermission
     */
    public function create(array $data): UserPermission;

    /**
     * Cập nhật quyền
     *
     * @param int $id
     * @param array $data
     * @return UserPermission
     */
    public function update(int $id, array $data): UserPermission;

    /**
     * Xóa quyền
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Xóa tất cả quyền của user
     *
     * @param int $userId
     * @return bool
     */
    public function deleteByUserId(int $userId): bool;

    /**
     * Cập nhật hàng loạt quyền cho user
     *
     * @param int $userId
     * @param array $permissions
     * @return void
     */
    public function syncPermissions(int $userId, array $permissions): void;
}

