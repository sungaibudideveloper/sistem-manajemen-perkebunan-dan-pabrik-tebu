<?php

namespace App\Services\UserManagement;

use App\Repositories\UserManagement\{JabatanRepository, UserRepository};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Pagination\LengthAwarePaginator;

class JabatanService
{
    protected JabatanRepository $jabatanRepository;
    protected UserRepository $userRepository;
    protected CacheService $cacheService;

    public function __construct(
        JabatanRepository $jabatanRepository,
        UserRepository $userRepository,
        CacheService $cacheService
    ) {
        $this->jabatanRepository = $jabatanRepository;
        $this->userRepository = $userRepository;
        $this->cacheService = $cacheService;
    }

    /**
     * Get paginated jabatan list
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedJabatan(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->jabatanRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get all jabatan
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllJabatan()
    {
        return $this->jabatanRepository->getAll();
    }

    /**
     * Get jabatan with permissions
     *
     * @param int $idjabatan
     * @return mixed
     */
    public function getJabatanWithPermissions(int $idjabatan)
    {
        return $this->jabatanRepository->findWithPermissions($idjabatan);
    }

    /**
     * Create new jabatan
     *
     * @param array $data
     * @param string $createdBy
     * @return array ['success' => bool, 'jabatan' => Jabatan|null, 'message' => string]
     */
    public function createJabatan(array $data, string $createdBy): array
    {
        try {
            // Check if name already exists
            if ($this->jabatanRepository->nameExists($data['namajabatan'])) {
                return [
                    'success' => false,
                    'jabatan' => null,
                    'message' => 'Nama jabatan sudah digunakan'
                ];
            }

            $jabatanData = [
                'namajabatan' => $data['namajabatan'],
                'inputby' => $createdBy,
                'createdat' => now()
            ];

            $jabatan = $this->jabatanRepository->create($jabatanData);

            Log::info('Jabatan created', [
                'idjabatan' => $jabatan->idjabatan,
                'created_by' => $createdBy
            ]);

            return [
                'success' => true,
                'jabatan' => $jabatan,
                'message' => 'Jabatan berhasil ditambahkan'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create jabatan', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'jabatan' => null,
                'message' => 'Gagal menambahkan jabatan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update jabatan
     *
     * @param int $idjabatan
     * @param array $data
     * @param string $updatedBy
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateJabatan(int $idjabatan, array $data, string $updatedBy): array
    {
        try {
            $jabatan = $this->jabatanRepository->find($idjabatan);

            if (!$jabatan) {
                return [
                    'success' => false,
                    'message' => 'Jabatan tidak ditemukan'
                ];
            }

            // Check if new name already exists (excluding current)
            if ($this->jabatanRepository->nameExists($data['namajabatan'], $idjabatan)) {
                return [
                    'success' => false,
                    'message' => 'Nama jabatan sudah digunakan'
                ];
            }

            $updateData = [
                'namajabatan' => $data['namajabatan'],
                'updateby' => $updatedBy,
                'updatedat' => now()
            ];

            $this->jabatanRepository->update($idjabatan, $updateData);

            // Clear cache for all users with this jabatan
            $this->cacheService->clearCacheForJabatan($idjabatan);

            Log::info('Jabatan updated', [
                'idjabatan' => $idjabatan,
                'updated_by' => $updatedBy
            ]);

            return [
                'success' => true,
                'message' => 'Jabatan berhasil diperbarui'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update jabatan', [
                'idjabatan' => $idjabatan,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui jabatan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete jabatan
     *
     * @param int $idjabatan
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteJabatan(int $idjabatan): array
    {
        try {
            $jabatan = $this->jabatanRepository->find($idjabatan);

            if (!$jabatan) {
                return [
                    'success' => false,
                    'message' => 'Jabatan tidak ditemukan'
                ];
            }

            // Check if jabatan is being used
            $userCount = $this->userRepository->countByJabatan($idjabatan);

            if ($userCount > 0) {
                return [
                    'success' => false,
                    'message' => "Jabatan sedang digunakan oleh {$userCount} user dan tidak bisa dihapus"
                ];
            }

            // Deactivate all permissions first
            $this->jabatanRepository->deactivateAllPermissions($idjabatan);

            // Clear cache before delete
            $this->cacheService->clearCacheForJabatan($idjabatan);

            // Delete jabatan
            $this->jabatanRepository->delete($idjabatan);

            Log::info('Jabatan deleted', [
                'idjabatan' => $idjabatan
            ]);

            return [
                'success' => true,
                'message' => 'Jabatan berhasil dihapus'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete jabatan', [
                'idjabatan' => $idjabatan,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus jabatan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Assign permissions to jabatan
     *
     * @param int $idjabatan
     * @param array $permissionIds
     * @param string $grantedBy
     * @return array ['success' => bool, 'message' => string]
     */
    public function assignPermissions(int $idjabatan, array $permissionIds, string $grantedBy): array
    {
        try {
            DB::beginTransaction();

            // Deactivate all existing permissions
            $this->jabatanRepository->deactivateAllPermissions($idjabatan);

            // Assign selected permissions
            foreach ($permissionIds as $permissionId) {
                $this->jabatanRepository->assignPermission($idjabatan, $permissionId, $grantedBy);
            }

            // Clear cache for all users with this jabatan
            $affectedUsers = $this->cacheService->clearCacheForJabatan($idjabatan);

            DB::commit();

            Log::info('Jabatan permissions assigned', [
                'idjabatan' => $idjabatan,
                'permissions_count' => count($permissionIds),
                'affected_users' => $affectedUsers,
                'granted_by' => $grantedBy
            ]);

            return [
                'success' => true,
                'message' => 'Permissions berhasil diperbarui'
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to assign jabatan permissions', [
                'idjabatan' => $idjabatan,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui permissions: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get jabatan permissions
     *
     * @param int $idjabatan
     * @return array
     */
    public function getJabatanPermissions(int $idjabatan): array
    {
        $permissions = $this->jabatanRepository->getPermissions($idjabatan);

        return [
            'permissions' => $permissions->map(function ($item) {
                return [
                    'id' => $item->permissionid,
                    'displayname' => $item->permission->displayname ?? '',
                    'module' => $item->permission->module ?? '',
                    'resource' => $item->permission->resource ?? '',
                    'action' => $item->permission->action ?? '',
                    'description' => $item->permission->description ?? ''
                ];
            })
        ];
    }

    /**
     * Get permission IDs for jabatan
     *
     * @param int $idjabatan
     * @return array
     */
    public function getPermissionIds(int $idjabatan): array
    {
        return $this->jabatanRepository->getPermissionIds($idjabatan);
    }
}