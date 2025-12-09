<?php

namespace App\Services\UserManagement;

use App\Repositories\UserManagement\{UserCompanyRepository, UserRepository};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Pagination\LengthAwarePaginator;

class UserCompanyService
{
    protected UserCompanyRepository $userCompanyRepository;
    protected UserRepository $userRepository;
    protected CacheService $cacheService;

    public function __construct(
        UserCompanyRepository $userCompanyRepository,
        UserRepository $userRepository,
        CacheService $cacheService
    ) {
        $this->userCompanyRepository = $userCompanyRepository;
        $this->userRepository = $userRepository;
        $this->cacheService = $cacheService;
    }

    /**
     * Get paginated users with company access
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedUserCompanies(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userCompanyRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get user companies
     *
     * @param string $userid
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserCompanies(string $userid)
    {
        return $this->userCompanyRepository->getUserCompanies($userid);
    }

    /**
     * Assign companies to user (multiple)
     *
     * @param string $userid
     * @param array $companycodes
     * @param string $grantedBy
     * @return array ['success' => bool, 'message' => string]
     */
    public function assignCompanies(string $userid, array $companycodes, string $grantedBy): array
    {
        try {
            DB::beginTransaction();

            $user = $this->userRepository->findWithRelations($userid);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            // Assign each company
            foreach ($companycodes as $companycode) {
                $this->userCompanyRepository->assignCompany($userid, $companycode, $grantedBy);
            }

            // Clear cache
            $this->cacheService->clearUserAndCompanyCache($user, 'Company access added');

            DB::commit();

            Log::info('Companies assigned to user', [
                'userid' => $userid,
                'companies_count' => count($companycodes),
                'granted_by' => $grantedBy
            ]);

            return [
                'success' => true,
                'message' => 'Company access berhasil ditambahkan'
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to assign companies', [
                'userid' => $userid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menambahkan company access: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update user company access (replace all)
     *
     * @param string $userid
     * @param array $companycodes
     * @param string $grantedBy
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateUserCompanies(string $userid, array $companycodes, string $grantedBy): array
    {
        try {
            DB::beginTransaction();

            $user = $this->userRepository->findWithRelations($userid);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            $this->userCompanyRepository->deleteAllForUser($userid);

            foreach ($companycodes as $companycode) {
                $this->userCompanyRepository->assignCompany($userid, $companycode, $grantedBy);
            }

            // Clear cache
            $this->cacheService->clearUserAndCompanyCache($user, 'Company access updated');

            DB::commit();

            Log::info('User company access updated', [
                'userid' => $userid,
                'companies_count' => count($companycodes),
                'granted_by' => $grantedBy
            ]);

            return [
                'success' => true,
                'message' => 'Company access berhasil diperbarui'
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update user companies', [
                'userid' => $userid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui company access: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove company access from user
     *
     * @param string $userid
     * @param string $companycode
     * @return array ['success' => bool, 'message' => string]
     */
    public function removeCompanyAccess(string $userid, string $companycode): array
    {
        try {
            $user = $this->userRepository->findWithRelations($userid);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            $this->userCompanyRepository->removeAccess($userid, $companycode);

            // Clear cache
            $this->cacheService->clearUserAndCompanyCache($user, 'Company access removed');

            Log::info('Company access removed', [
                'userid' => $userid,
                'companycode' => $companycode
            ]);

            return [
                'success' => true,
                'message' => 'Company access berhasil dihapus'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to remove company access', [
                'userid' => $userid,
                'companycode' => $companycode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus company access'
            ];
        }
    }

    /**
     * Get users without company access
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersWithoutAccess()
    {
        return $this->userCompanyRepository->getUsersWithoutAccess();
    }

    /**
     * Check if user has access to company
     *
     * @param string $userid
     * @param string $companycode
     * @return bool
     */
    public function hasAccess(string $userid, string $companycode): bool
    {
        return $this->userCompanyRepository->hasAccess($userid, $companycode);
    }
}