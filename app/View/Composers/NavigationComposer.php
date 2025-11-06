<?php

namespace App\View\Composers;

use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Middleware\CheckPermission;

class NavigationComposer
{
    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_TTL = 3600;

    /**
     * Request-level cache untuk permission array (in-memory)
     * Prevents repeated cache queries dalam single request
     */
    private static $requestPermissionCache = [];

    /**
     * Bind data to the view
     * Caches all navigation data per user to minimize database queries
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Generate unique cache key per user, jabatan, and company
            $cacheKey = "nav_data_{$user->userid}_{$user->idjabatan}_" . session('companycode');

            // Cache all navigation data to prevent repeated queries
            $navigationData = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
                return [
                    'navigationMenus' => $this->getNavigationMenus(),
                    'allSubmenus' => $this->getAllSubmenus(),
                    'userPermissions' => $this->getUserPermissions(), // ✅ Load once, store in cache
                    'companyName' => $this->getCompanyName(),
                    'user' => $this->getCurrentUserName(),
                    'userCompanies' => $this->getUserCompanies(),
                    'company' => $this->getUserCompanies(),
                    'period' => $this->getMonitoringPeriod()
                ];
            });

            // ✅ STORE permission array in request-level memory cache
            $requestCacheKey = $user->userid . '_' . $user->idjabatan;
            self::$requestPermissionCache[$requestCacheKey] = $navigationData['userPermissions'];

            $view->with($navigationData);
        }
    }

    /**
     * Get navigation menus for sidebar
     *
     * @return \Illuminate\Support\Collection
     */
    private function getNavigationMenus()
    {
        try {
            return DB::table('menu')
                ->orderBy('menuid')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting navigation menus: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get all submenus for sidebar
     *
     * @return \Illuminate\Support\Collection
     */
    private function getAllSubmenus()
    {
        try {
            return DB::table('submenu')
                ->orderBy('menuid')
                ->orderBy('submenuid')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting submenus: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get user permissions with additional caching layer
     * Returns array of granted permission names
     *
     * @return array
     */
    private function getUserPermissions()
    {
        try {
            if (!Auth::check()) {
                return [];
            }

            $user = Auth::user();

            // Additional cache layer for permission array
            // This prevents repeated calls to CheckPermission::getUserEffectivePermissions()
            $permCacheKey = "user_perms_array_{$user->userid}_{$user->idjabatan}";

            return Cache::remember($permCacheKey, self::CACHE_TTL, function () use ($user) {
                $effectivePermissions = CheckPermission::getUserEffectivePermissions($user);

                // Extract only granted permission names
                $grantedPermissions = [];
                foreach ($effectivePermissions as $permissionName => $details) {
                    if ($details['granted']) {
                        $grantedPermissions[] = $permissionName;
                    }
                }

                return $grantedPermissions;
            });
        } catch (\Exception $e) {
            \Log::error('Error getting user permissions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user has specific permission
     * Uses REQUEST-LEVEL in-memory cache to prevent repeated cache queries
     *
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission($permissionName)
    {
        try {
            if (!Auth::check()) {
                return false;
            }

            $user = Auth::user();
            $requestCacheKey = $user->userid . '_' . $user->idjabatan;

            // ✅ PRIORITY 1: Check request-level memory cache (NO DB QUERY)
            if (isset(self::$requestPermissionCache[$requestCacheKey])) {
                return in_array($permissionName, self::$requestPermissionCache[$requestCacheKey]);
            }

            // ✅ PRIORITY 2: Load from Laravel cache (1 DB query)
            $permCacheKey = "user_perms_array_{$user->userid}_{$user->idjabatan}";

            $grantedPermissions = Cache::remember($permCacheKey, self::CACHE_TTL, function () use ($user) {
                $effectivePermissions = CheckPermission::getUserEffectivePermissions($user);

                $grantedPermissions = [];
                foreach ($effectivePermissions as $permName => $details) {
                    if ($details['granted']) {
                        $grantedPermissions[] = $permName;
                    }
                }

                return $grantedPermissions;
            });

            // ✅ Store in request cache untuk calls berikutnya
            self::$requestPermissionCache[$requestCacheKey] = $grantedPermissions;

            return in_array($permissionName, $grantedPermissions);
        } catch (\Exception $e) {
            \Log::error('Error checking permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Convention-based permission mapping
     * Maps submenu slug to permission name
     * 
     * DEFAULT BEHAVIOR:
     * - Returns titleized slug as permission name
     * - Example: 'support-ticket' becomes 'Support Ticket'
     * 
     * OVERRIDE for special cases that don't follow convention
     *
     * @param string $menuSlug
     * @param string|null $submenuSlug
     * @return string
     */
    public function getPermissionName($menuSlug, $submenuSlug = null)
    {
        // Menu-level permissions
        $menuPermissions = [
            'masterdata' => 'Master',
            'input' => 'Input Data',
            'report' => 'Report',
            'dashboard' => 'Dashboard',
            'process' => 'Process',
            'usermanagement' => 'Kelola User'
        ];

        // Submenu-level permission overrides
        // Only for cases that don't follow the convention
        $submenuPermissionOverrides = [
            // Master Data - non-standard permissions
            'master-list' => 'MasterList',
            'herbisida-dosage' => 'Dosis Herbisida',
            'tenagakerja' => 'Tenaga Kerja',

            // Input Data - non-standard permissions
            'rencanakerjaharian' => 'Rencana Kerja Harian',
            'gudang-bbm' => 'Menu Gudang',
            'kendaraan-workshop' => 'Kendaraan',
            'pias' => 'Menu Pias',

            // Dashboard - non-standard permissions
            'agronomi-dashboard' => 'Dashboard Agronomi',
            'hpt-dashboard' => 'Dashboard HPT',

            // Report - non-standard permissions
            'agronomi-report' => 'Report Agronomi',
            'hpt-report' => 'Report HPT',

            // Process - non-standard permissions
            'upload-gpx-file' => 'Upload GPX File',
            'export-kml-file' => 'Export KML File',

            // User Management - non-standard permissions
            'user' => 'Kelola User',
            'user-company-permissions' => 'Kelola User',
            'user-activity-permission' => 'Kelola User',
            'user-permissions' => 'Kelola User',
            'permissions-masterdata' => 'Master',
            'jabatan' => 'Jabatan',
            'support-ticket' => 'Kelola User',
            'menu' => 'Menu',
            'submenu' => 'Submenu',
            'subsubmenu' => 'Subsubmenu',
        ];

        // Logic: Convention over configuration
        if ($submenuSlug) {
            // Check if there's an override for this slug
            if (isset($submenuPermissionOverrides[$submenuSlug])) {
                return $submenuPermissionOverrides[$submenuSlug];
            }

            // Convention: Titleize slug to get permission name
            // Example: 'support-ticket' becomes 'Support Ticket'
            return $this->slugToPermissionName($submenuSlug);
        }

        // Return menu-level permission
        return $menuPermissions[$menuSlug] ?? $this->slugToPermissionName($menuSlug);
    }

    /**
     * Convert slug to Permission Name (Title Case)
     * 
     * Examples:
     * - 'support-ticket' becomes 'Support Ticket'
     * - 'user' becomes 'User'
     * - 'company' becomes 'Company'
     *
     * @param string $slug
     * @return string
     */
    private function slugToPermissionName($slug)
    {
        // Replace hyphens/underscores with spaces
        $name = str_replace(['-', '_'], ' ', $slug);

        // Convert to title case
        return ucwords($name);
    }

    /**
     * Get company name for current session
     *
     * @return string
     */
    private function getCompanyName()
    {
        try {
            if (session('companycode')) {
                $companyName = DB::table('company')
                    ->where('companycode', session('companycode'))
                    ->value('name');

                if ($companyName) {
                    session(['companyname' => $companyName]);
                    return $companyName;
                }

                return session('companycode');
            }
            return 'Default Company';
        } catch (\Exception $e) {
            \Log::error('Error getting company name: ' . $e->getMessage());
            return 'Default Company';
        }
    }

    /**
     * Get monitoring period for header display
     *
     * @return string|null
     */
    private function getMonitoringPeriod()
    {
        try {
            if (session('companycode')) {
                $period = DB::table('company')
                    ->where('companycode', session('companycode'))
                    ->value('companyperiod');

                if ($period) {
                    return Carbon::parse($period)->format('F Y');
                }
            }
            return null;
        } catch (\Exception $e) {
            \Log::error('Error getting monitoring period: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current user name
     *
     * @return string
     */
    private function getCurrentUserName()
    {
        try {
            if (Auth::check()) {
                return DB::table('user')
                    ->where('userid', Auth::user()->userid)
                    ->value('name') ?? 'Guest';
            }
            return 'Guest';
        } catch (\Exception $e) {
            \Log::error('Error getting current user name: ' . $e->getMessage());
            return 'Guest';
        }
    }

    /**
     * Get user companies for current user
     *
     * @return array
     */
    private function getUserCompanies()
    {
        try {
            if (Auth::check()) {
                $companies = DB::table('usercompany')
                    ->where('userid', Auth::user()->userid)
                    ->where('isactive', 1)
                    ->pluck('companycode')
                    ->toArray();

                sort($companies);
                return $companies;
            }
            return [];
        } catch (\Exception $e) {
            \Log::error('Error getting user companies: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear navigation and permission caches for a user
     * Should be called when user permissions, jabatan, or company access changes
     *
     * @param \App\Models\User $user
     * @return void
     */
    public static function clearNavigationCache($user)
    {
        $companies = DB::table('usercompany')
            ->where('userid', $user->userid)
            ->where('isactive', 1)
            ->pluck('companycode');

        // Clear navigation cache for all companies user has access to
        foreach ($companies as $companycode) {
            $navCacheKey = "nav_data_{$user->userid}_{$user->idjabatan}_{$companycode}";
            Cache::forget($navCacheKey);
        }

        // Clear permission array cache
        $permCacheKey = "user_perms_array_{$user->userid}_{$user->idjabatan}";
        Cache::forget($permCacheKey);

        // ✅ Clear request-level cache
        $requestCacheKey = $user->userid . '_' . $user->idjabatan;
        unset(self::$requestPermissionCache[$requestCacheKey]);

        \Log::info('Navigation and permission array cache cleared', [
            'userid' => $user->userid,
            'jabatan' => $user->idjabatan,
            'companies_count' => $companies->count()
        ]);
    }
}
