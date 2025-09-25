<?php

namespace App\View\Composers;

use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\CheckPermission;

class NavigationComposer
{
    public function compose(View $view)
    {
        // Hanya load navigation data jika user sudah login
        if (Auth::check()) {
            $view->with([
                'navigationMenus' => $this->getNavigationMenus(),
                'allSubmenus' => $this->getAllSubmenus(),
                'userPermissions' => $this->getUserPermissions(),
                'companyName' => $this->getCompanyName(),
                'user' => $this->getCurrentUserName(),
                'userCompanies' => $this->getUserCompanies(),
                'company' => $this->getUserCompanies(),
                'period' => $this->getMonitoringPeriod()
            ]);
        }
    }

    /**
     * Get navigation menus for sidebar
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
     * Get user permissions - NEW: menggunakan sistem permission baru
     */
    private function getUserPermissions()
    {
        try {
            if (!Auth::check()) {
                return [];
            }

            $user = Auth::user();
            $effectivePermissions = CheckPermission::getUserEffectivePermissions($user);
            
            // Return array of permission names yang di-grant
            $grantedPermissions = [];
            foreach ($effectivePermissions as $permissionName => $details) {
                if ($details['granted']) {
                    $grantedPermissions[] = $permissionName;
                }
            }
            
            return $grantedPermissions;
            
        } catch (\Exception $e) {
            \Log::error('Error getting user permissions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permissionName)
    {
        try {
            if (!Auth::check()) {
                return false;
            }

            $user = Auth::user();
            $effectivePermissions = CheckPermission::getUserEffectivePermissions($user);
            
            return isset($effectivePermissions[$permissionName]) && 
                   $effectivePermissions[$permissionName]['granted'] === true;
            
        } catch (\Exception $e) {
            \Log::error('Error checking permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get permission name for menu/submenu
     */
    public function getPermissionName($menuSlug, $submenuSlug = null)
    {
        // Mapping berdasarkan data menu/submenu Anda
        $menuPermissions = [
            'masterdata' => 'Master',
            'input-data' => 'Input Data', 
            'report' => 'Report',
            'dashboard' => 'Dashboard',
            'process' => 'Process',
            'usermanagement' => 'Kelola User'
        ];

        $submenuPermissions = [
            // Master Data
            'company' => 'Company',
            'master-list' => 'MasterList',
            'blok' => 'Blok',
            'plotting' => 'Plotting',
            'kategori' => 'Kategori',
            'herbisida' => 'Herbisida',
            'herbisida-dosage' => 'Dosis Herbisida',
            'varietas' => 'Varietas',
            'mandor' => 'Mandor',
            'tenagakerja' => 'Tenaga Kerja',
            'jabatan' => 'Jabatan',
            'username' => 'Kelola User',
            'approval' => 'Approval',
            'aktivitas' => 'Aktivitas',
            'menu' => 'Menu',
            'submenu' => 'Submenu', 
            'subsubmenu' => 'Subsubmenu',
            'kendaraan' => 'Kendaraan',
            'upah' => 'Upah',
            'batch' => 'Batch',
            'accounting' => 'Accounting',

            // Input Data
            'kerja-harian' => 'Rencana Kerja Harian',
            'gudang' => 'Gudang',
            'gudang-bbm' => 'Menu Gudang',
            'kendaraan-workshop' => 'Kendaraan',
            'pias' => 'Menu Pias',
            'agronomi' => 'Agronomi',
            'hpt' => 'HPT',

            // Dashboard
            'agronomi-dashboard' => 'Dashboard Agronomi',
            'hpt-dashboard' => 'Dashboard HPT',
            'timeline' => 'Timeline',
            'maps' => 'Maps',

            // Report
            'agronomi-report' => 'Report Agronomi',
            'hpt-report' => 'Report HPT',

            // Process
            'posting' => 'Posting',
            'unposting' => 'Unposting',
            'upload gpx file' => 'Upload GPX File',
            'export kml file' => 'Export KML File', 
            'closing' => 'Closing',

            // User Management
            'user' => 'Kelola User',
            'user-company-permissions' => 'Hak Akses',
            'user-permissions' => 'Hak Akses', 
            'permissions-masterdata' => 'Hak Akses',
            'jabatan' => 'Jabatan'
        ];

        // Jika ada submenu, return permission submenu
        if ($submenuSlug && isset($submenuPermissions[$submenuSlug])) {
            return $submenuPermissions[$submenuSlug];
        }

        // Return permission menu
        return $menuPermissions[$menuSlug] ?? $menuSlug;
    }

    /**
     * Get company name
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
     * Get monitoring period for header
     */
    private function getMonitoringPeriod()
    {
        try {
            if (session('companycode')) {
                $period = DB::table('company')
                    ->where('companycode', session('companycode'))
                    ->value('updatedat');
                
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
     * Get user companies - FIXED: untuk sistem baru
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
}