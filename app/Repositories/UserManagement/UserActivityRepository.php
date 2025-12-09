<?php

namespace App\Repositories\UserManagement;

use App\Models\UserActivity;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserActivityRepository
{
    /**
     * Get paginated user activities
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = UserActivity::with(['user', 'company']);

        // Apply company filter
        if (!empty($filters['companycode'])) {
            $query->where('companycode', $filters['companycode']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('userid', 'like', "%{$search}%")
                  ->orWhere('activitygroup', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('createdat', 'desc')->paginate($perPage);
    }

    /**
     * Get user activity for specific company
     *
     * @param string $userid
     * @param string $companycode
     * @return UserActivity|null
     */
    public function getUserActivity(string $userid, string $companycode): ?UserActivity
    {
        return UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->first();
    }

    /**
     * Get all activities for user
     *
     * @param string $userid
     * @return Collection
     */
    public function getAllForUser(string $userid): Collection
    {
        return UserActivity::where('userid', $userid)->get();
    }

    /**
     * Assign activities to user
     *
     * @param string $userid
     * @param string $companycode
     * @param string $activitygroup
     * @param string $grantedBy
     * @return UserActivity
     */
    public function assignActivities(string $userid, string $companycode, string $activitygroup, string $grantedBy): UserActivity
    {
        return UserActivity::updateOrCreate(
            [
                'userid' => $userid,
                'companycode' => $companycode
            ],
            [
                'activitygroup' => $activitygroup,
                'grantedby' => $grantedBy,
                'createdat' => now(),
                'updatedat' => now()
            ]
        );
    }

    /**
     * Delete user activity
     *
     * @param string $userid
     * @param string $companycode
     * @return bool
     */
    public function delete(string $userid, string $companycode): bool
    {
        return UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->delete();
    }

    /**
     * Check if activity exists
     *
     * @param string $userid
     * @param string $companycode
     * @return bool
     */
    public function exists(string $userid, string $companycode): bool
    {
        return UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->exists();
    }

    /**
     * Get activity groups as array
     *
     * @param string $userid
     * @param string $companycode
     * @return array
     */
    public function getActivityGroupsArray(string $userid, string $companycode): array
    {
        $activity = $this->getUserActivity($userid, $companycode);
        
        if (!$activity || empty($activity->activitygroup)) {
            return [];
        }

        return array_filter(
            array_map('trim', explode(',', $activity->activitygroup))
        );
    }

    /**
     * Count activities for user
     *
     * @param string $userid
     * @return int
     */
    public function countForUser(string $userid): int
    {
        return UserActivity::where('userid', $userid)->count();
    }
}