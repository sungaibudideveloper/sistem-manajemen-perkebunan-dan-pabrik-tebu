<?php

namespace App\Repositories\UserManagement;

use App\Models\UserCompany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserCompanyRepository
{
    /**
     * Get paginated users with company access
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = \App\Models\User::with([
            'jabatan',
            'userCompanies' => function ($q) {
                $q->where('isactive', 1)->with('company');
            }
        ])
        ->whereHas('userCompanies', function ($q) {
            $q->where('isactive', 1);
        });

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('userid', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('userCompanies', function ($q2) use ($search) {
                      $q2->where('companycode', 'like', "%{$search}%")
                         ->where('isactive', 1);
                  });
            });
        }

        return $query->orderBy('userid')->paginate($perPage);
    }

    /**
     * Get user companies
     *
     * @param string $userid
     * @param bool $activeOnly
     * @return Collection
     */
    public function getUserCompanies(string $userid, bool $activeOnly = true): Collection
    {
        $query = UserCompany::where('userid', $userid)->with('company');

        if ($activeOnly) {
            $query->where('isactive', 1);
        }

        return $query->get();
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
        return UserCompany::where('userid', $userid)
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->exists();
    }

    /**
     * Assign company to user
     *
     * @param string $userid
     * @param string $companycode
     * @param string $grantedBy
     * @return UserCompany
     */
    public function assignCompany(string $userid, string $companycode, string $grantedBy): UserCompany
    {
        return UserCompany::updateOrCreate(
            [
                'userid' => $userid,
                'companycode' => $companycode
            ],
            [
                'isactive' => 1,
                'grantedby' => $grantedBy,
                'createdat' => now()
            ]
        );
    }

    /**
     * Deactivate all companies for user
     *
     * @param string $userid
     * @return bool
     */
    public function deactivateAllForUser(string $userid): bool
    {
        return UserCompany::where('userid', $userid)
            ->update(['isactive' => 0]);
    }

    /**
     * Remove company access
     *
     * @param string $userid
     * @param string $companycode
     * @return bool
     */
    public function removeAccess(string $userid, string $companycode): bool
    {
        return UserCompany::where('userid', $userid)
            ->where('companycode', $companycode)
            ->update([
                'isactive' => 0,
                'updatedat' => now()
            ]);
    }

    /**
     * Get users without company access
     *
     * @return Collection
     */
    public function getUsersWithoutAccess(): Collection
    {
        return \App\Models\User::with('jabatan')
            ->where('isactive', 1)
            ->whereDoesntHave('userCompanies', function ($q) {
                $q->where('isactive', 1);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Count companies for user
     *
     * @param string $userid
     * @return int
     */
    public function countForUser(string $userid): int
    {
        return UserCompany::where('userid', $userid)
            ->where('isactive', 1)
            ->count();
    }
}