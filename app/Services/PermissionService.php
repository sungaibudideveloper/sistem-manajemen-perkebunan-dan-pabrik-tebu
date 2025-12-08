<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Permission Service
 * 
 * BEST PRACTICE: Single source of truth for permission checking
 * Used by: Middleware, Blade directives, Controllers
 * 
 * Permission Format: module.resource.action
 * Examples:
 * - masterdata.company.view
 * - input.agronomi.create
 * - report.zpk.export
 */
class PermissionService
{
    /**
     * Cache duration (1 day)
     */
    const CACHE_TTL = 86400;

    /**
     * Request-level cache (in-memory) to prevent repeated cache lookups
     */
    private static $requestCache = [];

    /**
     * Check if user has permission
     * 
     * Logic:
     * 1. Check user-specific overrides (GRANT/DENY)
     * 2. If no override, check jabatan permissions
     * 3. Cache result for performance
     * 
     * @param User $user
     * @param string $permissionKey (e.g., 'masterdata.company.view')
     * @param string|null $companycode
     * @return bool
     */
    public static function check(User $user, string $permissionKey, ?string $companycode = null): bool
    {
        // Use session company if not provided
        $companycode = $companycode ?? session('companycode');
        
        // ✅ CRITICAL: Return false if no company selected (handle session lama/rusak)
        if (!$companycode) {
            return false;
        }
        
        // Request-level cache key
        $requestCacheKey = "{$user->userid}_{$user->idjabatan}_{$companycode}";

        // STEP 1: Check request-level cache (fastest - no DB, no Laravel cache)
        if (isset(self::$requestCache[$requestCacheKey])) {
            return in_array($permissionKey, self::$requestCache[$requestCacheKey]);
        }

        // STEP 2: Load permissions and store in request cache
        $permissions = self::getUserPermissions($user, $companycode);
        self::$requestCache[$requestCacheKey] = $permissions;

        return in_array($permissionKey, $permissions);
    }

    /**
     * Get all user permissions (with caching)
     * 
     * @param User $user
     * @param string|null $companycode
     * @return array Array of permission keys
     */
    public static function getUserPermissions(User $user, ?string $companycode = null): array
    {
        $companycode = $companycode ?? session('companycode');
        
        // ✅ Return empty if no company
        if (!$companycode) {
            return [];
        }
        
        // Cache key with APP_KEY hash to prevent encryption errors after key rotation
        $appKeyHash = substr(md5(config('app.key')), 0, 8);
        $cacheKey = "user_permissions_{$user->userid}_{$user->idjabatan}_{$companycode}_{$appKeyHash}";

        try {
            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $companycode) {
                return self::calculateUserPermissions($user, $companycode);
            });
        } catch (\Exception $e) {
            Log::warning('Permission cache error, rebuilding...', [
                'error' => $e->getMessage(),
                'user' => $user->userid
            ]);

            // Clear corrupted cache
            Cache::forget($cacheKey);

            // Rebuild without cache
            return self::calculateUserPermissions($user, $companycode);
        }
    }

    /**
     * Calculate user permissions (core logic)
     * 
     * Priority:
     * 1. User-specific DENY overrides (highest priority)
     * 2. User-specific GRANT overrides
     * 3. Jabatan (role) permissions
     * 
     * @param User $user
     * @param string|null $companycode  ✅ UBAH: nullable
     * @return array
     */
    private static function calculateUserPermissions(User $user, ?string $companycode): array
    {
        $permissions = [];

        // STEP 1: Get jabatan (role) permissions
        $jabatanPermissions = DB::table('jabatanpermission as jp')
            ->join('permission as p', 'jp.permissionid', '=', 'p.id')
            ->where('jp.idjabatan', $user->idjabatan)
            ->where('jp.isactive', 1)
            ->where('p.isactive', 1)
            ->select(
                'p.module',
                'p.resource',
                'p.action'
            )
            ->get();

        foreach ($jabatanPermissions as $perm) {
            $key = "{$perm->module}.{$perm->resource}.{$perm->action}";
            $permissions[$key] = true;
        }

        // ✅ STEP 2: Apply user-specific overrides (only if companycode exists)
        if ($companycode) {
            $userOverrides = DB::table('userpermission as up')
                ->join('permission as p', 'up.permissionid', '=', 'p.id')
                ->where('up.userid', $user->userid)
                ->where('up.companycode', $companycode)
                ->where('up.isactive', 1)
                ->where('p.isactive', 1)
                ->select(
                    'p.module',
                    'p.resource',
                    'p.action',
                    'up.permissiontype'
                )
                ->get();

            foreach ($userOverrides as $override) {
                $key = "{$override->module}.{$override->resource}.{$override->action}";
                
                if ($override->permissiontype === 'GRANT') {
                    $permissions[$key] = true;
                } elseif ($override->permissiontype === 'DENY') {
                    unset($permissions[$key]); // Remove permission
                }
            }
        }

        return array_keys($permissions);
    }

    /**
     * Check multiple permissions (OR logic)
     * User has ANY of the permissions
     * 
     * @param User $user
     * @param array $permissionKeys
     * @param string|null $companycode
     * @return bool
     */
    public static function checkAny(User $user, array $permissionKeys, ?string $companycode = null): bool
    {
        foreach ($permissionKeys as $permission) {
            if (self::check($user, $permission, $companycode)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check multiple permissions (AND logic)
     * User has ALL of the permissions
     * 
     * @param User $user
     * @param array $permissionKeys
     * @param string|null $companycode
     * @return bool
     */
    public static function checkAll(User $user, array $permissionKeys, ?string $companycode = null): bool
    {
        foreach ($permissionKeys as $permission) {
            if (!self::check($user, $permission, $companycode)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Build permission key from components
     * 
     * @param string $module
     * @param string $resource
     * @param string $action
     * @return string
     */
    public static function makeKey(string $module, string $resource, string $action): string
    {
        return "{$module}.{$resource}.{$action}";
    }

    /**
     * Parse permission key into components
     * 
     * @param string $permissionKey
     * @return array ['module' => '...', 'resource' => '...', 'action' => '...']
     */
    public static function parseKey(string $permissionKey): array
    {
        $parts = explode('.', $permissionKey);
        
        return [
            'module' => $parts[0] ?? null,
            'resource' => $parts[1] ?? null,
            'action' => $parts[2] ?? null,
        ];
    }

    /**
     * Clear user permission cache
     * Call this when:
     * - User jabatan changes
     * - Jabatan permissions change
     * - User permission overrides change
     * 
     * @param User $user
     * @return void
     */
    public static function clearUserCache(User $user): void
    {
        try {
            $appKeyHash = substr(md5(config('app.key')), 0, 8);
            
            // Get all companies user has access to
            $companies = DB::table('usercompany')
                ->where('userid', $user->userid)
                ->where('isactive', 1)
                ->pluck('companycode');

            // Clear cache for all companies
            foreach ($companies as $companycode) {
                $cacheKey = "user_permissions_{$user->userid}_{$user->idjabatan}_{$companycode}_{$appKeyHash}";
                Cache::forget($cacheKey);
            }

            // Clear request-level cache
            $requestCacheKey = "{$user->userid}_{$user->idjabatan}_" . session('companycode');
            unset(self::$requestCache[$requestCacheKey]);

            // Also clear old cache keys (without APP_KEY hash) for cleanup
            foreach ($companies as $companycode) {
                $oldKey = "user_permissions_{$user->userid}_{$user->idjabatan}_{$companycode}";
                Cache::forget($oldKey);
            }

            Log::info('Permission cache cleared', [
                'userid' => $user->userid,
                'jabatan' => $user->idjabatan,
                'companies_count' => $companies->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing permission cache', [
                'userid' => $user->userid ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear permission cache for entire jabatan (role)
     * Call this when jabatan permissions are updated
     * 
     * @param int $idjabatan
     * @return int Number of users affected
     */
    public static function clearJabatanCache(int $idjabatan): int
    {
        try {
            $users = DB::table('user')
                ->where('idjabatan', $idjabatan)
                ->where('isactive', 1)
                ->get();

            foreach ($users as $user) {
                $userModel = User::find($user->userid);
                if ($userModel) {
                    self::clearUserCache($userModel);
                }
            }

            Log::info('Jabatan permission cache cleared', [
                'idjabatan' => $idjabatan,
                'affected_users' => $users->count()
            ]);

            return $users->count();
        } catch (\Exception $e) {
            Log::error('Error clearing jabatan cache', [
                'idjabatan' => $idjabatan,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get permission ID from key
     * 
     * @param string $permissionKey
     * @return int|null
     */
    public static function getPermissionId(string $permissionKey): ?int
    {
        $parts = self::parseKey($permissionKey);
        
        return DB::table('permission')
            ->where('module', $parts['module'])
            ->where('resource', $parts['resource'])
            ->where('action', $parts['action'])
            ->where('isactive', 1)
            ->value('id');
    }

    /**
     * Check if permission exists in database
     * 
     * @param string $permissionKey
     * @return bool
     */
    public static function exists(string $permissionKey): bool
    {
        return self::getPermissionId($permissionKey) !== null;
    }
}