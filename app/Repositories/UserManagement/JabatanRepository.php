<?php

namespace App\Repositories\UserManagement;

use App\Models\{Jabatan, JabatanPermission};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class JabatanRepository
{
    /**
     * Get paginated jabatan with permission count
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Jabatan::withCount(['jabatanPermissions' => function ($q) {
            $q->where('isactive', 1);
        }]);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where('namajabatan', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('idjabatan', 'asc')->paginate($perPage);
    }

    /**
     * Get all jabatan
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Jabatan::orderBy('namajabatan')->get();
    }

    /**
     * Find jabatan by ID
     *
     * @param int $idjabatan
     * @return Jabatan|null
     */
    public function find(int $idjabatan): ?Jabatan
    {
        return Jabatan::find($idjabatan);
    }

    /**
     * Find jabatan with permissions
     *
     * @param int $idjabatan
     * @return Jabatan|null
     */
    public function findWithPermissions(int $idjabatan): ?Jabatan
    {
        return Jabatan::with(['jabatanPermissions' => function ($q) {
            $q->where('isactive', 1)->with('permission');
        }])->find($idjabatan);
    }

    /**
     * Create new jabatan
     *
     * @param array $data
     * @return Jabatan
     */
    public function create(array $data): Jabatan
    {
        return Jabatan::create($data);
    }

    /**
     * Update jabatan
     *
     * @param int $idjabatan
     * @param array $data
     * @return bool
     */
    public function update(int $idjabatan, array $data): bool
    {
        return Jabatan::where('idjabatan', $idjabatan)->update($data);
    }

    /**
     * Delete jabatan
     *
     * @param int $idjabatan
     * @return bool
     */
    public function delete(int $idjabatan): bool
    {
        return Jabatan::where('idjabatan', $idjabatan)->delete();
    }

    /**
     * Check if jabatan exists
     *
     * @param int $idjabatan
     * @return bool
     */
    public function exists(int $idjabatan): bool
    {
        return Jabatan::where('idjabatan', $idjabatan)->exists();
    }

    /**
     * Check if jabatan name exists
     *
     * @param string $namajabatan
     * @param int|null $excludeId
     * @return bool
     */
    public function nameExists(string $namajabatan, ?int $excludeId = null): bool
    {
        $query = Jabatan::where('namajabatan', $namajabatan);

        if ($excludeId) {
            $query->where('idjabatan', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get jabatan permissions
     *
     * @param int $idjabatan
     * @return Collection
     */
    public function getPermissions(int $idjabatan): Collection
    {
        return JabatanPermission::where('idjabatan', $idjabatan)
            ->where('isactive', 1)
            ->with('permission')
            ->get();
    }

    /**
     * Get permission IDs for jabatan
     *
     * @param int $idjabatan
     * @return array
     */
    public function getPermissionIds(int $idjabatan): array
    {
        return JabatanPermission::where('idjabatan', $idjabatan)
            ->where('isactive', 1)
            ->pluck('permissionid')
            ->toArray();
    }

    /**
     * Deactivate all permissions for jabatan
     *
     * @param int $idjabatan
     * @return bool
     */
    public function deactivateAllPermissions(int $idjabatan): bool
    {
        return JabatanPermission::where('idjabatan', $idjabatan)
            ->update(['isactive' => 0]);
    }

    /**
     * Assign permission to jabatan
     *
     * @param int $idjabatan
     * @param int $permissionid
     * @param string $grantedBy
     * @return JabatanPermission
     */
    public function assignPermission(int $idjabatan, int $permissionid, string $grantedBy): JabatanPermission
    {
        return JabatanPermission::updateOrCreate(
            [
                'idjabatan' => $idjabatan,
                'permissionid' => $permissionid
            ],
            [
                'isactive' => 1,
                'grantedby' => $grantedBy,
                'createdat' => now()
            ]
        );
    }

    /**
     * Count active permissions for jabatan
     *
     * @param int $idjabatan
     * @return int
     */
    public function countActivePermissions(int $idjabatan): int
    {
        return JabatanPermission::where('idjabatan', $idjabatan)
            ->where('isactive', 1)
            ->count();
    }
}