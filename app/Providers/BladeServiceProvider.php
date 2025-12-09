<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Services\PermissionService;

/**
 * Blade Service Provider
 * 
 * Custom Blade directives for permission checking in views
 * 
 * Usage in Blade:
 * @can('masterdata.company.view')
 *     <button>View Company</button>
 * @endcan
 * 
 * @canany(['masterdata.company.view', 'masterdata.company.edit'])
 *     <button>Manage Company</button>
 * @endcanany
 */
class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // @can directive - check single permission
        Blade::if('can', function (string $permission) {
            if (!Auth::check()) {
                return false;
            }
            return PermissionService::check(Auth::user(), $permission);
        });

        // @canany directive - check multiple permissions (OR logic)
        Blade::if('canany', function (array $permissions) {
            if (!Auth::check()) {
                return false;
            }
            return PermissionService::checkAny(Auth::user(), $permissions);
        });

        // @canall directive - check multiple permissions (AND logic)
        Blade::if('canall', function (array $permissions) {
            if (!Auth::check()) {
                return false;
            }
            return PermissionService::checkAll(Auth::user(), $permissions);
        });

        // @cannot directive - inverse of @can
        Blade::if('cannot', function (string $permission) {
            if (!Auth::check()) {
                return true; // Cannot do anything if not logged in
            }
            return !PermissionService::check(Auth::user(), $permission);
        });
    }
}

/**
 * USAGE EXAMPLES:
 * 
 * 1. Single permission check:
 * @can('masterdata.company.view')
 *     <a href="{{ route('masterdata.company.index') }}">View Companies</a>
 * @endcan
 * 
 * 2. Check ANY of multiple permissions:
 * @canany(['masterdata.company.view', 'masterdata.company.edit'])
 *     <div class="company-section">
 *         ...
 *     </div>
 * @endcanany
 * 
 * 3. Check ALL permissions:
 * @canall(['masterdata.company.view', 'masterdata.company.edit'])
 *     <button>Full Access</button>
 * @endcanall
 * 
 * 4. Inverse check:
 * @cannot('masterdata.company.delete')
 *     <p class="text-muted">You cannot delete companies</p>
 * @endcannot
 * 
 * 5. Combined with @else:
 * @can('masterdata.company.edit')
 *     <button class="btn-primary">Edit</button>
 * @else
 *     <button class="btn-secondary" disabled>Edit (No Permission)</button>
 * @endcan
 */