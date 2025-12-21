<?php

namespace App\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\User;
use App\Models\Screen;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    /**
     * @var PermissionRepositoryInterface
     */
    protected $permissionRepository;

    /**
     * PermissionService constructor.
     *
     * @param PermissionRepositoryInterface $permissionRepository
     */
    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Lấy danh sách users không phải admin
     *
     * @return Collection
     */
    public function getNonAdminUsers(): Collection
    {
        return User::where('role', '!=', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }

    /**
     * Lấy danh sách screens dạng tree
     *
     * @return Collection
     */
    public function getScreensTree(): Collection
    {
        return Screen::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('order')
                    ->with(['children' => function ($q) {
                        $q->where('is_active', true)->orderBy('order');
                    }]);
            }])
            ->get();
    }

    /**
     * Lấy danh sách screens dạng flat
     *
     * @return Collection
     */
    public function getScreensFlat(): Collection
    {
        return Screen::getFlatTree();
    }

    /**
     * Lấy quyền của một user theo dạng key-value
     *
     * @param User $user
     * @return array
     */
    public function getUserPermissions(User $user): array
    {
        $permissions = $this->permissionRepository->getByUserId($user->id);

        return $permissions->keyBy('screen_id')->map(function ($permission) {
            return [
                'can_view' => $permission->can_view,
                'can_create' => $permission->can_create,
                'can_edit' => $permission->can_edit,
                'can_delete' => $permission->can_delete,
            ];
        })->toArray();
    }

    /**
     * Cập nhật quyền của user
     *
     * @param User $user
     * @param array $permissions
     * @return void
     */
    public function updateUserPermissions(User $user, array $permissions): void
    {
        $this->permissionRepository->syncPermissions($user->id, $permissions);
    }
}

