<?php

namespace App\Repositories\UserManagement;

use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class PermissionRepository
{
    /**
     * Get paginated permissions with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Permission::query();

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('displayname', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('resource', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply module filter
        if (!empty($filters['modules'])) {
            $query->whereIn('module', $filters['modules']);
        }

        // Apply active status filter
        if (isset($filters['isactive'])) {
            $query->where('isactive', $filters['isactive']);
        }

        return $query->orderBy('id')->paginate($perPage);
    }

    /**
     * Get all active permissions grouped by module
     *
     * @return SupportCollection
     */
    public function getAllGroupedByModule(): SupportCollection
    {
        return Permission::where('isactive', 1)
            ->orderBy('module')
            ->orderBy('displayname')
            ->get()
            ->groupBy('module');
    }

    /**
     * Get distinct modules
     *
     * @return SupportCollection
     */
    public function getModules(): SupportCollection
    {
        return Permission::distinct()
            ->where('isactive', 1)
            ->pluck('module')
            ->filter()
            ->sort()
            ->values();
    }

    /**
     * Find permission by ID
     *
     * @param int $id
     * @return Permission|null
     */
    public function find(int $id): ?Permission
    {
        return Permission::find($id);
    }

    /**
     * Find permission by display name
     *
     * @param string $displayname
     * @return Permission|null
     */
    public function findByDisplayName(string $displayname): ?Permission
    {
        return Permission::where('displayname', $displayname)->first();
    }

    /**
     * Find permission by module.resource.action
     *
     * @param string $module
     * @param string $resource
     * @param string $action
     * @return Permission|null
     */
    public function findByComposite(string $module, string $resource, string $action): ?Permission
    {
        return Permission::where('module', $module)
            ->where('resource', $resource)
            ->where('action', $action)
            ->first();
    }

    /**
     * Create new permission
     *
     * @param array $data
     * @return Permission
     */
    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    /**
     * Update permission
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return Permission::where('id', $id)->update($data);
    }

    /**
     * Soft delete permission (set isactive = 0)
     *
     * @param int $id
     * @return bool
     */
    public function softDelete(int $id): bool
    {
        return Permission::where('id', $id)->update(['isactive' => 0]);
    }

    /**
     * Check if permission exists by display name
     *
     * @param string $displayname
     * @param int|null $excludeId
     * @return bool
     */
    public function displayNameExists(string $displayname, ?int $excludeId = null): bool
    {
        $query = Permission::where('displayname', $displayname);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if permission composite exists
     *
     * @param string $module
     * @param string $resource
     * @param string $action
     * @param int|null $excludeId
     * @return bool
     */
    public function compositeExists(string $module, string $resource, string $action, ?int $excludeId = null): bool
    {
        $query = Permission::where('module', $module)
            ->where('resource', $resource)
            ->where('action', $action);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get permissions by module
     *
     * @param string $module
     * @param bool $activeOnly
     * @return Collection
     */
    public function getByModule(string $module, bool $activeOnly = true): Collection
    {
        $query = Permission::where('module', $module);

        if ($activeOnly) {
            $query->where('isactive', 1);
        }

        return $query->orderBy('displayname')->get();
    }

    /**
     * Check if permission is being used
     *
     * @param int $id
     * @return array ['jabatan_count' => int, 'user_count' => int]
     */
    public function getUsageCount(int $id): array
    {
        return [
            'jabatan_count' => \App\Models\JabatanPermission::where('permissionid', $id)
                ->where('isactive', 1)
                ->count(),
            'user_count' => \App\Models\UserPermission::where('permissionid', $id)
                ->where('isactive', 1)
                ->count()
        ];
    }
}