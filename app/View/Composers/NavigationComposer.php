<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PermissionService;

/**
 * Navigation Composer
 * 
 * BEST PRACTICE: View composer for sidebar navigation
 * Reads menu from config/menu.php (not database)
 * Filters menu items based on user permissions
 */
class NavigationComposer
{
    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();

            try {
                // Get menu structure from config file
                $menuConfig = config('menu', []);

                // Filter menu based on permissions
                $navigationMenus = $this->filterMenuByPermissions($menuConfig, $user);

                $view->with([
                    'navigationMenus' => collect($navigationMenus),
                    'companyName' => $this->getCompanyName(),
                    'user' => $this->getCurrentUserName(),
                    'company' => $this->getUserCompanies(), // ✅ FIXED: Keep as 'company' for backward compatibility
                    'period' => $this->getMonitoringPeriod(),
                ]);

            } catch (\Exception $e) {
                Log::error('Navigation composer error', [
                    'error' => $e->getMessage(),
                    'user' => $user->userid
                ]);

                // Fallback: empty menu
                $view->with([
                    'navigationMenus' => collect([]),
                    'companyName' => $this->getCompanyName(),
                    'user' => $this->getCurrentUserName(),
                    'company' => $this->getUserCompanies(), // ✅ FIXED
                    'period' => $this->getMonitoringPeriod(),
                ]);
            }
        }
    }

    /**
     * Filter menu items based on user permissions (recursive)
     * 
     * @param array $menuItems
     * @param \App\Models\User $user
     * @return array
     */
    private function filterMenuByPermissions(array $menuItems, $user): array
    {
        $filtered = [];

        foreach ($menuItems as $item) {
            // Check if user has permission for this menu item
            if (isset($item['permission'])) {
                if (!PermissionService::check($user, $item['permission'])) {
                    continue; // Skip this item
                }
            }

            // Process children recursively
            if (isset($item['children']) && is_array($item['children'])) {
                $filteredChildren = $this->filterMenuByPermissions($item['children'], $user);
                
                // Only include parent if it has visible children OR has a route itself
                if (!empty($filteredChildren)) {
                    $item['children'] = $filteredChildren;
                    $filtered[] = $item;
                } elseif (isset($item['route'])) {
                    // Parent has route and user has permission
                    unset($item['children']); // Remove empty children
                    $filtered[] = $item;
                }
            } else {
                // Leaf node (no children)
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    /**
     * Check if user has permission (for backward compatibility)
     * 
     * @param string $permissionKey
     * @return bool
     */
    public function hasPermission(string $permissionKey): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return PermissionService::check(Auth::user(), $permissionKey);
    }

    /**
     * Get company name for current session
     *
     * @return string
     */
    private function getCompanyName(): string
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
            Log::error('Error getting company name: ' . $e->getMessage());
            return 'Default Company';
        }
    }

    /**
     * Get monitoring period for header display
     *
     * @return string|null
     */
    private function getMonitoringPeriod(): ?string
    {
        try {
            if (session('companycode')) {
                $period = DB::table('company')
                    ->where('companycode', session('companycode'))
                    ->value('companyperiod');

                if ($period) {
                    return \Carbon\Carbon::parse($period)->format('F Y');
                }
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Error getting monitoring period: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current user name
     *
     * @return string
     */
    private function getCurrentUserName(): string
    {
        try {
            if (Auth::check()) {
                return DB::table('user')
                    ->where('userid', Auth::user()->userid)
                    ->value('name') ?? 'Guest';
            }
            return 'Guest';
        } catch (\Exception $e) {
            Log::error('Error getting current user name: ' . $e->getMessage());
            return 'Guest';
        }
    }

    /**
     * Get user companies for current user
     *
     * @return array
     */
    private function getUserCompanies(): array
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
            Log::error('Error getting user companies: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear navigation cache for a user
     * (Now just clears permission cache since menu is config-based)
     * 
     * @param \App\Models\User $user
     * @return void
     */
    public static function clearNavigationCache($user): void
    {
        PermissionService::clearUserCache($user);
    }

    /**
     * Clear all navigation caches
     * (Now just clears all permission caches)
     * 
     * @return void
     */
    public static function clearAllNavigationCaches(): void
    {
        try {
            // Clear all permission caches
            if (config('cache.default') === 'redis') {
                $redis = \Illuminate\Support\Facades\Cache::getRedis();
                
                $permKeys = $redis->keys('*user_permissions_*');
                foreach ($permKeys as $key) {
                    \Illuminate\Support\Facades\Cache::forget(
                        str_replace(config('cache.prefix') . ':', '', $key)
                    );
                }
                
                Log::info('All permission caches cleared via Redis');
            } else {
                // Fallback: clear all cache
                \Illuminate\Support\Facades\Cache::flush();
                Log::info('All caches cleared (including permissions)');
            }

        } catch (\Exception $e) {
            Log::error('Error clearing all caches: ' . $e->getMessage());
        }
    }
}