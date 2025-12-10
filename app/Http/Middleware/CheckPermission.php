<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PermissionService;
use App\Models\User;

/**
 * Check Permission Middleware
 * 
 * BEST PRACTICE: Route protection with permission checking
 * Uses new PermissionService for consistency
 * 
 * Usage:
 * Route::middleware('permission:masterdata.company.view')
 */
class CheckPermission
{
    /**
     * Cache duration (1 hour)
     */
    const CACHE_TTL = 3600;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $permission (can be comma-separated for OR logic)
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            abort(401, 'Unauthenticated');
        }

        $user = Auth::user();

        // Handle multiple permissions (OR logic)
        $permissions = array_map('trim', explode(',', $permission));

        // Check if user has ANY of the required permissions
        if (PermissionService::checkAny($user, $permissions)) {
            return $next($request);
        }

        // Permission denied - log for debugging
        Log::warning('Permission denied', [
            'user' => $user->name,
            'userid' => $user->userid,
            'required_permission' => $permission,
            'user_jabatan' => $user->idjabatan,
            'session_company' => session('companycode')
        ]);

        abort(403, 'Unauthorized action. Required permission: ' . $permission);
    }

    // =========================================================================
    // PUBLIC API METHODS (For backward compatibility & programmatic checks)
    // =========================================================================

    /**
     * Check if user has specific permission (for programmatic checks)
     * 
     * @param User $user
     * @param string $permissionKey
     * @param string|null $companycode
     * @return bool
     */
    public static function checkUserPermission($user, string $permissionKey, ?string $companycode = null): bool
    {
        return PermissionService::check($user, $permissionKey, $companycode);
    }

    /**
     * Get all effective permissions for a user
     * Returns detailed array with source, category, granted status
     * 
     * BACKWARD COMPATIBLE: Returns old format for existing code
     * 
     * @param User $user
     * @return array
     */
    public static function getUserEffectivePermissions(User $user): array
    {
        $cacheKey = "user_permissions_detailed_{$user->userid}_{$user->idjabatan}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $effectivePermissions = [];

            // STEP 1: Load Jabatan Permissions
            if ($user->idjabatan) {
                $jabatanPermissions = DB::table('jabatanpermission as jp')
                    ->join('permission as p', 'jp.permissionid', '=', 'p.id')
                    ->where('jp.idjabatan', $user->idjabatan)
                    ->where('jp.isactive', 1)
                    ->where('p.isactive', 1)
                    ->select(
                        'p.module',
                        'p.resource',
                        'p.action',
                        'p.displayname'
                    )
                    ->get();

                foreach ($jabatanPermissions as $perm) {
                    $key = "{$perm->module}.{$perm->resource}.{$perm->action}";
                    $effectivePermissions[$key] = [
                        'source' => 'jabatan',
                        'category' => $perm->module,
                        'granted' => true,
                        'display_name' => $perm->displayname
                    ];
                }
            }

            // STEP 2: Load User Companies
            $userCompanies = DB::table('usercompany')
                ->where('userid', $user->userid)
                ->where('isactive', 1)
                ->pluck('companycode')
                ->toArray();

            // STEP 3: Load User-Specific Overrides
            $userPermissions = DB::table('userpermission as up')
                ->join('permission as p', 'up.permissionid', '=', 'p.id')
                ->where('up.userid', $user->userid)
                ->where('up.isactive', 1)
                ->where('p.isactive', 1)
                ->select(
                    'p.module',
                    'p.resource',
                    'p.action',
                    'p.displayname',
                    'up.permissiontype',
                    'up.companycode'
                )
                ->get();

            // STEP 4: Apply User Overrides
            foreach ($userPermissions as $perm) {
                $hasCompanyAccess = in_array($perm->companycode, $userCompanies);

                if ($hasCompanyAccess) {
                    $key = "{$perm->module}.{$perm->resource}.{$perm->action}";
                    
                    if ($perm->permissiontype === 'GRANT') {
                        $effectivePermissions[$key] = [
                            'source' => 'user_override',
                            'category' => $perm->module,
                            'granted' => true,
                            'company' => $perm->companycode,
                            'display_name' => $perm->displayname
                        ];
                    } elseif ($perm->permissiontype === 'DENY') {
                        // Remove permission
                        unset($effectivePermissions[$key]);
                    }
                }
            }

            return $effectivePermissions;
        });
    }

    /**
     * Get granted permission keys only (array of strings)
     * Used in views/blade templates
     * 
     * @param User $user
     * @return array
     */
    public static function getGrantedPermissions(User $user): array
    {
        $effectivePermissions = self::getUserEffectivePermissions($user);

        $grantedPermissions = [];
        foreach ($effectivePermissions as $permissionKey => $details) {
            if ($details['granted']) {
                $grantedPermissions[] = $permissionKey;
            }
        }

        return $grantedPermissions;
    }

    // =========================================================================
    // COMPANY ACCESS METHODS (IMPORTANT - Keep for existing code)
    // =========================================================================

    /**
     * Check if user has access to specific company
     * 
     * @param User $user
     * @param string $companycode
     * @return bool
     */
    public static function hasCompanyAccess(User $user, string $companycode): bool
    {
        $userCompanies = self::getUserCompanies($user);
        return in_array($companycode, $userCompanies);
    }

    /**
     * Get user's accessible companies (cached)
     * 
     * @param User $user
     * @return array
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

    // =========================================================================
    // CACHE MANAGEMENT
    // =========================================================================

    /**
     * Clear user permission cache
     * 
     * @param User $user
     * @return void
     */
    public static function clearUserCache(User $user): void
    {
        // Clear new permission service cache
        PermissionService::clearUserCache($user);

        // Clear legacy detailed permission cache
        $detailedCacheKey = "user_permissions_detailed_{$user->userid}_{$user->idjabatan}";
        Cache::forget($detailedCacheKey);

        // Clear company cache
        $companyCacheKey = "user_companies_{$user->userid}";
        Cache::forget($companyCacheKey);

        Log::info('Cleared all permission caches', [
            'userid' => $user->userid,
            'jabatan' => $user->idjabatan
        ]);
    }

    /**
     * Clear jabatan permission cache
     * 
     * @param int $idjabatan
     * @return int
     */
    public static function clearJabatanCache(int $idjabatan): int
    {
        return PermissionService::clearJabatanCache($idjabatan);
    }

    /**
     * Refresh user permission cache
     * Clears and reloads immediately
     * 
     * @param User $user
     * @return array
     */
    public static function refreshUserCache(User $user): array
    {
        self::clearUserCache($user);
        return self::getUserEffectivePermissions($user);
    }

    // =========================================================================
    // DEBUG & TROUBLESHOOTING
    // =========================================================================

    /**
     * Debug permission for troubleshooting
     * Shows detailed info about why user has/doesn't have permission
     * 
     * @param User $user
     * @param string $permissionKey
     * @return array
     */
    public static function debugPermission(User $user, string $permissionKey): array
    {
        $effectivePermissions = self::getUserEffectivePermissions($user);
        $hasPermission = isset($effectivePermissions[$permissionKey]) && 
                        $effectivePermissions[$permissionKey]['granted'];

        // Get permission details from database
        $parts = explode('.', $permissionKey);
        $permissionInDb = DB::table('permission')
            ->where('module', $parts[0] ?? '')
            ->where('resource', $parts[1] ?? '')
            ->where('action', $parts[2] ?? '')
            ->first();

        return [
            'user' => [
                'userid' => $user->userid,
                'name' => $user->name,
                'jabatan' => $user->idjabatan,
                'company' => session('companycode')
            ],
            'permission' => [
                'key' => $permissionKey,
                'exists_in_db' => $permissionInDb !== null,
                'is_active' => $permissionInDb->isactive ?? false,
            ],
            'result' => [
                'has_permission' => $hasPermission,
                'details' => $effectivePermissions[$permissionKey] ?? null,
            ],
            'stats' => [
                'total_permissions' => count($effectivePermissions),
                'granted_count' => count(array_filter($effectivePermissions, fn($p) => $p['granted'])),
            ],
            'cache_keys' => [
                'permission_service' => PermissionService::makeKey($parts[0] ?? '', $parts[1] ?? '', $parts[2] ?? ''),
                'detailed' => "user_permissions_detailed_{$user->userid}_{$user->idjabatan}",
                'companies' => "user_companies_{$user->userid}",
            ]
        ];
    }

    /**
     * Get all permissions with details (for admin UI)
     * 
     * @param User $user
     * @return array
     */
    public static function getAllPermissionsDetailed(User $user): array
    {
        return self::getUserEffectivePermissions($user);
    }
}