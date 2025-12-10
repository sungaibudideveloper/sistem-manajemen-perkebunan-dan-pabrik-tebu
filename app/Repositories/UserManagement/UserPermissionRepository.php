<?php

namespace App\Repositories\UserManagement;

use App\Models\UserPermission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserPermissionRepository
{
    /**
     * Get paginated user permission overrides
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = UserPermission::with(['user.jabatan', 'permission']);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('userid', 'like', "%{$search}%")
                  ->orWhere('companycode', 'like', "%{$search}%")
                  ->orWhereHas('permission', function ($q2) use ($search) {
                      $q2->where('displayname', 'like', "%{$search}%");
                  });
            });
        }

        // Apply company filter
        if (!empty($filters['companycode'])) {
            $query->where('companycode', $filters['companycode']);
        }

        // Apply permission type filter
        if (!empty($filters['permissiontype'])) {
            $query->where('permissiontype', $filters['permissiontype']);
        }

        return $query->where('isactive', 1)
            ->orderBy('userid')
            ->orderBy('companycode')
            ->paginate($perPage);
    }

    /**
     * Get user permission overrides
     *
     * @param string $userid
     * @param string|null $companycode
     * @return Collection
     */
    public function getUserPermissions(string $userid, ?string $companycode = null): Collection
    {
        $query = UserPermission::where('userid', $userid)
            ->where('isactive', 1)
            ->with('permission');

        if ($companycode) {
            $query->where('companycode', $companycode);
        }

        return $query->get();
    }

    /**
     * Create permission override
     *
     * @param array $data
     * @return UserPermission
     */
    public function create(array $data): UserPermission
    {
        return UserPermission::updateOrCreate(
            [
                'userid' => $data['userid'],
                'companycode' => $data['companycode'],
                'permissionid' => $data['permissionid']
            ],
            [
                'permissiontype' => $data['permissiontype'],
                'isactive' => 1,
                'reason' => $data['reason'] ?? null,
                'grantedby' => $data['grantedby'],
                'createdat' => now()
            ]
        );
    }

    /**
     * Delete permission override
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return UserPermission::where('id', $id)
            ->update([
                'isactive' => 0,
                'updatedat' => now()
            ]);
    }

    /**
     * Find permission override
     *
     * @param int $id
     * @return UserPermission|null
     */
    public function find(int $id): ?UserPermission
    {
        return UserPermission::find($id);
    }

    /**
     * Check if override exists
     *
     * @param string $userid
     * @param string $companycode
     * @param int $permissionid
     * @return bool
     */
    public function exists(string $userid, string $companycode, int $permissionid): bool
    {
        return UserPermission::where('userid', $userid)
            ->where('companycode', $companycode)
            ->where('permissionid', $permissionid)
            ->where('isactive', 1)
            ->exists();
    }

    /**
     * Count overrides for user
     *
     * @param string $userid
     * @return int
     */
    public function countForUser(string $userid): int
    {
        return UserPermission::where('userid', $userid)
            ->where('isactive', 1)
            ->count();
    }
}