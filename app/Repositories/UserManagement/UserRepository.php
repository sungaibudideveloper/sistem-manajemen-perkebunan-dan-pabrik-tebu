<?php

namespace App\Repositories\UserManagement;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /**
     * Get paginated users with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = User::with(['jabatan', 'userCompanies', 'userActivities']);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('userid', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('companycode', 'like', "%{$search}%")
                  ->orWhereHas('jabatan', function ($q2) use ($search) {
                      $q2->where('namajabatan', 'like', "%{$search}%");
                  });
            });
        }

        // Apply company filter
        if (!empty($filters['companycode'])) {
            $query->where('companycode', $filters['companycode']);
        }

        // Apply jabatan filter
        if (!empty($filters['idjabatan'])) {
            $query->where('idjabatan', $filters['idjabatan']);
        }

        // Apply active status filter
        if (isset($filters['isactive'])) {
            $query->where('isactive', $filters['isactive']);
        }

        return $query->orderBy('createdat', 'desc')->paginate($perPage);
    }

    /**
     * Find user by ID with relations
     *
     * @param string $userid
     * @param array $relations
     * @return User|null
     */
    public function findWithRelations(string $userid, array $relations = []): ?User
    {
        $query = User::where('userid', $userid);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    /**
     * Create new user
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update user
     *
     * @param string $userid
     * @param array $data
     * @return bool
     */
    public function update(string $userid, array $data): bool
    {
        return User::where('userid', $userid)->update($data);
    }

    /**
     * Soft delete user (set isactive = 0)
     *
     * @param string $userid
     * @return bool
     */
    public function softDelete(string $userid): bool
    {
        return User::where('userid', $userid)->update([
            'isactive' => 0,
            'updatedat' => now()
        ]);
    }

    /**
     * Check if user exists
     *
     * @param string $userid
     * @return bool
     */
    public function exists(string $userid): bool
    {
        return User::where('userid', $userid)->exists();
    }

    /**
     * Get users by jabatan
     *
     * @param int $idjabatan
     * @param bool $activeOnly
     * @return Collection
     */
    public function getByJabatan(int $idjabatan, bool $activeOnly = true): Collection
    {
        $query = User::where('idjabatan', $idjabatan);

        if ($activeOnly) {
            $query->where('isactive', 1);
        }

        return $query->get();
    }

    /**
     * Get users by company
     *
     * @param string $companycode
     * @param bool $activeOnly
     * @return Collection
     */
    public function getByCompany(string $companycode, bool $activeOnly = true): Collection
    {
        $query = User::where('companycode', $companycode);

        if ($activeOnly) {
            $query->where('isactive', 1);
        }

        return $query->get();
    }

    /**
     * Get users with company access
     *
     * @param string $companycode
     * @return Collection
     */
    public function getUsersWithCompanyAccess(string $companycode): Collection
    {
        return User::whereHas('userCompanies', function ($q) use ($companycode) {
            $q->where('companycode', $companycode)
              ->where('isactive', 1);
        })->where('isactive', 1)->get();
    }

    /**
     * Count users by jabatan
     *
     * @param int $idjabatan
     * @return int
     */
    public function countByJabatan(int $idjabatan): int
    {
        return User::where('idjabatan', $idjabatan)
            ->where('isactive', 1)
            ->count();
    }

    public function getMandorByCompany(string $companyCode): Collection
    {
        return User::select(['userid', 'name', 'companycode', 'idjabatan'])
            ->where('companycode', $companyCode)
            ->where('idjabatan', 5)
            ->where('isactive', 1)
            ->orderBy('name')
            ->get();
    }

    public function getOperatorsByCompany(string $companyCode): Collection
    {
        return User::select(['userid', 'name', 'companycode', 'idjabatan'])
            ->where('companycode', $companyCode)
            ->whereIn('idjabatan', [3, 4])
            ->where('isactive', 1)
            ->orderBy('name')
            ->get();
    }
}