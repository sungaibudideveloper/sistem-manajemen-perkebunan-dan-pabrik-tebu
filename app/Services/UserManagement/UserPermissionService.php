<?php

namespace App\Services\UserManagement;

use App\Repositories\UserManagement\{UserPermissionRepository, UserRepository, UserCompanyRepository};
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class UserPermissionService
{
    protected UserPermissionRepository $userPermissionRepository;
    protected UserRepository $userRepository;
    protected UserCompanyRepository $userCompanyRepository;
    protected CacheService $cacheService;

    public function __construct(
        UserPermissionRepository $userPermissionRepository,
        UserRepository $userRepository,
        UserCompanyRepository $userCompanyRepository,
        CacheService $cacheService
    ) {
        $this->userPermissionRepository = $userPermissionRepository;
        $this->userRepository = $userRepository;
        $this->userCompanyRepository = $userCompanyRepository;
        $this->cacheService = $cacheService;
    }

    /**
     * Get paginated permission overrides
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedPermissionOverrides(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userPermissionRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get user permission overrides
     *
     * @param string $userid
     * @param string|null $companycode
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserPermissions(string $userid, ?string $companycode = null)
    {
        return $this->userPermissionRepository->getUserPermissions($userid, $companycode);
    }

    /**
     * Create permission override
     *
     * @param array $data
     * @param string $grantedBy
     * @return array ['success' => bool, 'message' => string]
     */
    public function createPermissionOverride(array $data, string $grantedBy): array
    {
        try {
            // Validate user exists
            $user = $this->userRepository->findWithRelations($data['userid']);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            // Validate user has company access
            if (!$this->userCompanyRepository->hasAccess($data['userid'], $data['companycode'])) {
                return [
                    'success' => false,
                    'message' => 'User tidak memiliki akses ke company yang dipilih'
                ];
            }

            // Check if already exists
            if ($this->userPermissionRepository->exists($data['userid'], $data['companycode'], $data['permissionid'])) {
                return [
                    'success' => false,
                    'message' => 'Permission override sudah ada untuk user ini'
                ];
            }

            // Create override
            $data['grantedby'] = $grantedBy;
            $this->userPermissionRepository->create($data);

            // Clear user cache
            $this->cacheService->clearUserCache($user, 'Permission override created');

            Log::info('Permission override created', [
                'userid' => $data['userid'],
                'permissionid' => $data['permissionid'],
                'type' => $data['permissiontype'],
                'granted_by' => $grantedBy
            ]);

            return [
                'success' => true,
                'message' => 'Permission override berhasil ditambahkan'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create permission override', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menambahkan permission override: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete permission override
     *
     * @param int $id
     * @return array ['success' => bool, 'message' => string]
     */
    public function deletePermissionOverride(int $id): array
    {
        try {
            $override = $this->userPermissionRepository->find($id);

            if (!$override) {
                return [
                    'success' => false,
                    'message' => 'Permission override tidak ditemukan'
                ];
            }

            $user = $this->userRepository->findWithRelations($override->userid);

            $this->userPermissionRepository->delete($id);

            // Clear user cache
            if ($user) {
                $this->cacheService->clearUserCache($user, 'Permission override removed');
            }

            Log::info('Permission override deleted', [
                'id' => $id,
                'userid' => $override->userid
            ]);

            return [
                'success' => true,
                'message' => 'Permission override berhasil dihapus'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete permission override', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus permission override'
            ];
        }
    }

    /**
     * Count overrides for user
     *
     * @param string $userid
     * @return int
     */
    public function countForUser(string $userid): int
    {
        return $this->userPermissionRepository->countForUser($userid);
    }
}