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
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedActivities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userActivityRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get user activity for company
     *
     * @param string $userid
     * @param string $companycode
     * @return array
     */
    public function getUserActivity(string $userid, string $companycode): array
    {
        return $this->userActivityRepository->getActivityGroupsArray($userid, $companycode);
    }

    /**
     * Assign activity groups to user
     *
     * @param string $userid
     * @param string $companycode
     * @param array $activitygroups
     * @param string $grantedBy
     * @return array ['success' => bool, 'message' => string]
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

            // Clean and join activity groups
            $cleanedActivities = array_filter(array_map('trim', $activitygroups));
            $activityString = implode(',', $cleanedActivities);

            // Assign activities
            $this->userActivityRepository->assignActivities($userid, $companycode, $activityString, $grantedBy);

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
     * Delete user activity
     *
     * @param string $userid
     * @param string $companycode
     * @return array ['success' => bool, 'message' => string]
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

            $this->userActivityRepository->delete($userid, $companycode);

            Log::info('User activity deleted', [
                'userid' => $userid,
                'companycode' => $companycode
            ]);

            return [
                'success' => true,
                'message' => 'Activity group berhasil dihapus'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete user activity', [
                'userid' => $userid,
                'companycode' => $companycode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus activity group'
            ];
        }
    }

    /**
     * Get all activities for user
     *
     * @param string $userid
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUserActivities(string $userid)
    {
        return $this->userActivityRepository->getAllForUser($userid);
    }

    /**
     * Count activities for user
     *
     * @param string $userid
     * @return int
     */
    public function countForUser(string $userid): int
    {
        return $this->userActivityRepository->countForUser($userid);
    }
}