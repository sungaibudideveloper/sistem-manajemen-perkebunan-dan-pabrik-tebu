<?php

namespace App\Services\UserManagement;

use App\Repositories\UserManagement\PermissionRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionService
{
    protected PermissionRepository $permissionRepository;
    protected CacheService $cacheService;

    public function __construct(
        PermissionRepository $permissionRepository,
        CacheService $cacheService
    ) {
        $this->permissionRepository = $permissionRepository;
        $this->cacheService = $cacheService;
    }

    /**
     * Get paginated permissions
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedPermissions(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->permissionRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get all permissions grouped by module
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllGroupedByModule()
    {
        return $this->permissionRepository->getAllGroupedByModule();
    }

    /**
     * Get all available modules
     *
     * @return \Illuminate\Support\Collection
     */
    public function getModules()
    {
        return $this->permissionRepository->getModules();
    }

    /**
     * Get permission by ID
     *
     * @param int $id
     * @return mixed
     */
    public function getPermissionById(int $id)
    {
        return $this->permissionRepository->find($id);
    }

    /**
     * Create new permission
     *
     * @param array $data
     * @return array ['success' => bool, 'permission' => Permission|null, 'message' => string]
     */
    public function createPermission(array $data): array
    {
        try {
            // Validate uniqueness
            if ($this->permissionRepository->displayNameExists($data['displayname'])) {
                return [
                    'success' => false,
                    'permission' => null,
                    'message' => 'Display name sudah digunakan'
                ];
            }

            if ($this->permissionRepository->compositeExists($data['module'], $data['resource'], $data['action'])) {
                return [
                    'success' => false,
                    'permission' => null,
                    'message' => 'Kombinasi module.resource.action sudah ada'
                ];
            }

            $permissionData = [
                'module' => $data['module'],
                'resource' => $data['resource'],
                'action' => $data['action'],
                'displayname' => $data['displayname'],
                'description' => $data['description'] ?? null,
                'isactive' => $data['isactive'] ?? 1,
                'createdat' => now()
            ];

            $permission = $this->permissionRepository->create($permissionData);

            // Clear navigation menu cache
            $this->cacheService->clearMenuCache();

            Log::info('Permission created', [
                'id' => $permission->id,
                'displayname' => $permission->displayname
            ]);

            return [
                'success' => true,
                'permission' => $permission,
                'message' => 'Permission berhasil ditambahkan'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create permission', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'permission' => null,
                'message' => 'Gagal menambahkan permission: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update permission
     *
     * @param int $id
     * @param array $data
     * @return array ['success' => bool, 'message' => string]
     */
    public function updatePermission(int $id, array $data): array
    {
        try {
            $permission = $this->permissionRepository->find($id);

            if (!$permission) {
                return [
                    'success' => false,
                    'message' => 'Permission tidak ditemukan'
                ];
            }

            // Validate uniqueness (excluding current)
            if ($this->permissionRepository->displayNameExists($data['displayname'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Display name sudah digunakan'
                ];
            }

            if ($this->permissionRepository->compositeExists($data['module'], $data['resource'], $data['action'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Kombinasi module.resource.action sudah ada'
                ];
            }

            $oldDisplayName = $permission->displayname;

            $updateData = [
                'module' => $data['module'],
                'resource' => $data['resource'],
                'action' => $data['action'],
                'displayname' => $data['displayname'],
                'description' => $data['description'] ?? null,
                'isactive' => $data['isactive'] ?? 1,
                'updatedat' => now()
            ];

            $this->permissionRepository->update($id, $updateData);

            // Clear cache if display name changed
            if ($oldDisplayName !== $data['displayname']) {
                $this->cacheService->clearCacheForPermission($oldDisplayName);
            }

            // Clear menu cache
            $this->cacheService->clearMenuCache();

            Log::info('Permission updated', [
                'id' => $id,
                'displayname' => $data['displayname']
            ]);

            return [
                'success' => true,
                'message' => 'Permission berhasil diperbarui'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update permission', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui permission: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Soft delete permission
     *
     * @param int $id
     * @return array ['success' => bool, 'message' => string]
     */
    public function deletePermission(int $id): array
    {
        try {
            $permission = $this->permissionRepository->find($id);

            if (!$permission) {
                return [
                    'success' => false,
                    'message' => 'Permission tidak ditemukan'
                ];
            }

            // Check if permission is being used
            $usage = $this->permissionRepository->getUsageCount($id);

            if ($usage['jabatan_count'] > 0 || $usage['user_count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'Permission sedang digunakan dan tidak bisa dihapus'
                ];
            }

            // Clear cache before delete
            $this->cacheService->clearCacheForPermission($permission->displayname);

            // Soft delete
            $this->permissionRepository->softDelete($id);

            // Clear menu cache
            $this->cacheService->clearMenuCache();

            Log::info('Permission deleted', [
                'id' => $id,
                'displayname' => $permission->displayname
            ]);

            return [
                'success' => true,
                'message' => 'Permission berhasil dinonaktifkan'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete permission', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menonaktifkan permission: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get permissions by module
     *
     * @param string $module
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissionsByModule(string $module, bool $activeOnly = true)
    {
        return $this->permissionRepository->getByModule($module, $activeOnly);
    }

    /**
     * Check if permission is being used
     *
     * @param int $id
     * @return array ['jabatan_count' => int, 'user_count' => int, 'total' => int]
     */
    public function getPermissionUsage(int $id): array
    {
        $usage = $this->permissionRepository->getUsageCount($id);
        $usage['total'] = $usage['jabatan_count'] + $usage['user_count'];

        return $usage;
    }
}