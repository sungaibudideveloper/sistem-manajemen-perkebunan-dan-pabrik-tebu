<?php

namespace App\Services\UserManagement;

use App\Repositories\UserManagement\{UserActivityRepository, UserRepository, UserCompanyRepository};
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class UserActivityService
{
    protected UserActivityRepository $userActivityRepository;
    protected UserRepository $userRepository;
    protected UserCompanyRepository $userCompanyRepository;

    public function __construct(
        UserActivityRepository $userActivityRepository,
        UserRepository $userRepository,
        UserCompanyRepository $userCompanyRepository
    ) {
        $this->userActivityRepository = $userActivityRepository;
        $this->userRepository = $userRepository;
        $this->userCompanyRepository = $userCompanyRepository;
    }

    /**
     * Get paginated user activities
     */
    public function getPaginatedActivities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userActivityRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get user activity for company (as array of activity codes)
     */
    public function getUserActivity(string $userid, string $companycode): array
    {
        return $this->userActivityRepository->getActivityGroupsArray($userid, $companycode);
    }

    /**
     * Assign activity groups to user (replaces all existing)
     */
    public function assignActivityGroups(string $userid, string $companycode, array $activitygroups, string $grantedBy): array
    {
        try {
            // Validate user exists
            $user = $this->userRepository->findWithRelations($userid);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            // Validate user has company access
            if (!$this->userCompanyRepository->hasAccess($userid, $companycode)) {
                return [
                    'success' => false,
                    'message' => 'User tidak memiliki akses ke company yang dipilih'
                ];
            }

            // Clean activity groups
            $cleanedActivities = array_filter(array_map('trim', $activitygroups));

            if (empty($cleanedActivities)) {
                // If empty, delete all
                $this->userActivityRepository->deleteAll($userid, $companycode);
            } else {
                // Sync activities
                $this->userActivityRepository->syncActivities($userid, $companycode, $cleanedActivities, $grantedBy);
            }

            Log::info('Activity groups assigned', [
                'userid' => $userid,
                'companycode' => $companycode,
                'activities_count' => count($cleanedActivities),
                'granted_by' => $grantedBy
            ]);

            return [
                'success' => true,
                'message' => 'Activity groups berhasil diperbarui'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to assign activity groups', [
                'userid' => $userid,
                'companycode' => $companycode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui activity groups: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete all user activities for company
     */
    public function deleteUserActivity(string $userid, string $companycode): array
    {
        try {
            if (!$this->userActivityRepository->exists($userid, $companycode)) {
                return [
                    'success' => false,
                    'message' => 'Activity assignment tidak ditemukan'
                ];
            }

            $this->userActivityRepository->deleteAll($userid, $companycode);

            Log::info('User activities deleted', [
                'userid' => $userid,
                'companycode' => $companycode
            ]);

            return [
                'success' => true,
                'message' => 'Semua activity groups berhasil dihapus'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete user activities', [
                'userid' => $userid,
                'companycode' => $companycode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus activity groups'
            ];
        }
    }

    /**
     * Count activities for user
     */
    public function countForUser(string $userid): int
    {
        return $this->userActivityRepository->countForUser($userid);
    }
}