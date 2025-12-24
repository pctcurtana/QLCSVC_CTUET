<?php

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * @var UserPermission
     */
    protected $model;

    /**
     * PermissionRepository constructor.
     *
     * @param UserPermission $model
     */
    public function __construct(UserPermission $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByUserAndScreen(int $userId, int $screenId): ?UserPermission
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('screen_id', $screenId)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): UserPermission
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): UserPermission
    {
        $permission = $this->model->findOrFail($id);
        $permission->update($data);
        return $permission->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $permission = $this->model->findOrFail($id);
        return $permission->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByUserId(int $userId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function syncPermissions(int $userId, array $permissions): void
    {
        // Xóa tất cả quyền cũ của user
        $this->deleteByUserId($userId);

        // Thêm quyền mới
        foreach ($permissions as $permission) {
            // Chỉ tạo bản ghi nếu có ít nhất 1 quyền
            if ($this->hasAnyPermission($permission)) {
                $this->model->create([
                    'user_id' => $userId,
                    'screen_id' => $permission['screen_id'],
                    'can_view' => $permission['can_view'] ?? false,
                    'can_create' => $permission['can_create'] ?? false,
                    'can_edit' => $permission['can_edit'] ?? false,
                    'can_delete' => $permission['can_delete'] ?? false,
                ]);
            }
        }
    }

    /**
     * Kiểm tra có ít nhất 1 quyền không
     *
     * @param array $permission
     * @return bool
     */
    private function hasAnyPermission(array $permission): bool
    {
        return ($permission['can_view'] ?? false) ||
               ($permission['can_create'] ?? false) ||
               ($permission['can_edit'] ?? false) ||
               ($permission['can_delete'] ?? false);
    }
}

