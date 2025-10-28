<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Permission;

class CheckPermission
{
    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_TTL = 3600;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (!Auth::check()) {
            abort(401, 'Unauthenticated');
        }
        
        $user = Auth::user();
        
        if (!$this->hasPermission($user, $permission)) {
            Log::warning('Permission denied', [
                'user' => $user->name,
                'userid' => $user->userid,
                'required_permission' => $permission,
                'user_jabatan' => $user->idjabatan,
                'session_company' => session('companycode')
            ]);
            
            abort(403, 'Unauthorized action. Required permission: ' . $permission);
        }
        
        return $next($request);
    }

    /**
     * Check if user has specific permission
     */
    private function hasPermission(User $user, string $permissionName): bool
    {
        $effectivePermissions = $this->getUserEffectivePermissions($user);
        
        return isset($effectivePermissions[$permissionName]) && 
               $effectivePermissions[$permissionName]['granted'] === true;
    }

    /**
     * Get all effective permissions for a user with caching
     * 
     * OPTIMIZED: 
     * - Cache results per user
     * - Single query for jabatan permissions
     * - Single query for user permissions  
     * - Single query for user companies (no N+1)
     * 
     * @param User $user
     * @return array
     */
    public static function getUserEffectivePermissions(User $user): array
    {
        $cacheKey = self::getCacheKey($user);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return self::loadUserPermissions($user);
        });
    }

    /**
     * Load user permissions from database (without cache)
     * Called only on cache miss
     */
    private static function loadUserPermissions(User $user): array
    {
        $effectivePermissions = [];
        
        // =====================================================================
        // STEP 1: Load Jabatan Permissions (1 Query)
        // =====================================================================
        if ($user->idjabatan) {
            $jabatanPermissions = DB::table('jabatanpermissions')
                ->join('permissions', 'jabatanpermissions.permissionid', '=', 'permissions.permissionid')
                ->where('jabatanpermissions.idjabatan', $user->idjabatan)
                ->where('jabatanpermissions.isactive', 1)
                ->where('permissions.isactive', 1)
                ->select('permissions.permissionname', 'permissions.category')
                ->get();

            foreach ($jabatanPermissions as $perm) {
                $effectivePermissions[$perm->permissionname] = [
                    'source' => 'jabatan',
                    'category' => $perm->category,
                    'granted' => true
                ];
            }
            
            Log::info('Loaded jabatan permissions', [
                'userid' => $user->userid,
                'jabatan' => $user->idjabatan,
                'count' => $jabatanPermissions->count()
            ]);
        }

        // =====================================================================
        // STEP 2: Load User Companies ONCE (1 Query)
        // =====================================================================
        $userCompanies = DB::table('usercompany')
            ->where('userid', $user->userid)
            ->where('isactive', 1)
            ->pluck('companycode')
            ->toArray();

        // =====================================================================
        // STEP 3: Load User-Specific Permission Overrides (1 Query)
        // =====================================================================
        $userPermissions = DB::table('userpermission')
            ->join('permissions', 'userpermission.permissionid', '=', 'permissions.permissionid')
            ->where('userpermission.userid', $user->userid)
            ->where('userpermission.isactive', 1)
            ->where('permissions.isactive', 1)
            ->select(
                'permissions.permissionname', 
                'permissions.category', 
                'userpermission.permissiontype', 
                'userpermission.companycode'
            )
            ->get();

        // =====================================================================
        // STEP 4: Apply User Overrides (No Additional Queries)
        // =====================================================================
        foreach ($userPermissions as $perm) {
            // Check company access using in-memory array (NO QUERY!)
            $hasCompanyAccess = in_array($perm->companycode, $userCompanies);

            if ($hasCompanyAccess) {
                $effectivePermissions[$perm->permissionname] = [
                    'source' => 'user_override',
                    'category' => $perm->category,
                    'granted' => $perm->permissiontype === 'GRANT',
                    'company' => $perm->companycode
                ];
            }
        }

        Log::info('Loaded user permissions (CACHE MISS)', [
            'userid' => $user->userid,
            'total_permissions' => count($effectivePermissions),
            'user_overrides' => $userPermissions->count()
        ]);

        return $effectivePermissions;
    }

    /**
     * Get granted permission names only (for blade views)
     * 
     * @param User $user
     * @return array
     */
    public static function getGrantedPermissions(User $user): array
    {
        $effectivePermissions = self::getUserEffectivePermissions($user);
        
        $grantedPermissions = [];
        foreach ($effectivePermissions as $permissionName => $details) {
            if ($details['granted']) {
                $grantedPermissions[] = $permissionName;
            }
        }
        
        return $grantedPermissions;
    }

    /**
     * Check if permission exists in master data
     * Used for validation before checking user permissions
     */
    private static function permissionExists(string $permissionName): bool
    {
        static $permissionCache = [];
        
        if (!isset($permissionCache[$permissionName])) {
            $permissionCache[$permissionName] = Permission::where('permissionname', $permissionName)
                ->where('isactive', 1)
                ->exists();
        }
        
        return $permissionCache[$permissionName];
    }

    /**
     * Generate cache key for user permissions
     */
    private static function getCacheKey(User $user): string
    {
        return "user_permissions_{$user->userid}_{$user->idjabatan}";
    }

    /**
     * Clear user permission cache
     * Call this after:
     * - User logout
     * - Permission changes
     * - Jabatan changes
     * - Company access changes
     */
    public static function clearUserCache(User $user): void
    {
        $cacheKey = self::getCacheKey($user);
        Cache::forget($cacheKey);
        
        Log::info('Cleared permission cache', [
            'userid' => $user->userid,
            'cache_key' => $cacheKey
        ]);
    }

    /**
     * Clear all user permission caches
     * Call this after bulk permission updates
     */
    public static function clearAllUserCaches(): void
    {
        // Note: This requires you to track active users or use cache tags
        // For now, this is a placeholder for manual implementation
        
        Log::warning('clearAllUserCaches called - implement cache tag flushing if needed');
        
        // If using Redis with cache tags:
        // Cache::tags(['user_permissions'])->flush();
    }

    /**
     * Refresh user permission cache
     * Useful for immediate permission updates without logout
     */
    public static function refreshUserCache(User $user): array
    {
        self::clearUserCache($user);
        return self::getUserEffectivePermissions($user);
    }

    /**
     * Check if user has access to specific company
     * OPTIMIZED: Uses cached user companies
     */
    public static function hasCompanyAccess(User $user, string $companycode): bool
    {
        $cacheKey = "user_companies_{$user->userid}";
        
        $userCompanies = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return DB::table('usercompany')
                ->where('userid', $user->userid)
                ->where('isactive', 1)
                ->pluck('companycode')
                ->toArray();
        });
        
        return in_array($companycode, $userCompanies);
    }

    /**
     * Get user's accessible companies (cached)
     */
    public static function getUserCompanies(User $user): array
    {
        $cacheKey = "user_companies_{$user->userid}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return DB::table('usercompany')
                ->where('userid', $user->userid)
                ->where('isactive', 1)
                ->pluck('companycode')
                ->toArray();
        });
    }

    /**
     * Debug: Get permission details for specific permission
     * Useful for troubleshooting
     */
    public static function debugPermission(User $user, string $permissionName): array
    {
        $effectivePermissions = self::getUserEffectivePermissions($user);
        
        return [
            'user' => [
                'userid' => $user->userid,
                'name' => $user->name,
                'jabatan' => $user->idjabatan
            ],
            'permission' => $permissionName,
            'has_permission' => isset($effectivePermissions[$permissionName]) && 
                              $effectivePermissions[$permissionName]['granted'],
            'details' => $effectivePermissions[$permissionName] ?? null,
            'all_permissions_count' => count($effectivePermissions),
            'cache_key' => self::getCacheKey($user)
        ];
    }
}