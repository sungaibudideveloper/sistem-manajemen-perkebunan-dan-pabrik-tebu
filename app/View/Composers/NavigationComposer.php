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
     * ✨ NEW: Convention-based permission mapping
     * Maps submenu slug to permission name
     * 
     * DEFAULT BEHAVIOR:
     * - Returns titleized slug as permission name
     * - Example: 'support-ticket' → 'Support Ticket'
     * 
     * OVERRIDE untuk special cases yang tidak mengikuti convention
     */
    public function getPermissionName($menuSlug, $submenuSlug = null)
    {
        // ============================================
        // MENU-LEVEL PERMISSIONS
        // ============================================
        $menuPermissions = [
            'masterdata' => 'Master',
            'input-data' => 'Input Data', 
            'report' => 'Report',
            'dashboard' => 'Dashboard',
            'process' => 'Process',
            'usermanagement' => 'Kelola User'
        ];

        // ============================================
        // SUBMENU-LEVEL PERMISSION OVERRIDES
        // Hanya untuk yang TIDAK mengikuti convention
        // ============================================
        $submenuPermissionOverrides = [
            // Master Data - yang tidak standard
            'master-list' => 'MasterList',
            'herbisida-dosage' => 'Dosis Herbisida',
            'tenagakerja' => 'Tenaga Kerja',
            
            // Input Data - yang tidak standard
            'kerja-harian' => 'Rencana Kerja Harian',
            'gudang-bbm' => 'Menu Gudang',
            'kendaraan-workshop' => 'Kendaraan',
            'pias' => 'Menu Pias',

            // Dashboard - yang tidak standard
            'agronomi-dashboard' => 'Dashboard Agronomi',
            'hpt-dashboard' => 'Dashboard HPT',

            // Report - yang tidak standard
            'agronomi-report' => 'Report Agronomi',
            'hpt-report' => 'Report HPT',

            // Process - yang tidak standard
            'upload-gpx-file' => 'Upload GPX File',
            'export-kml-file' => 'Export KML File',

            // User Management - yang tidak standard
            'user' => 'Kelola User',
            'user-company-permissions' => 'Kelola User',
            'user-permissions' => 'Kelola User',
            'permissions-masterdata' => 'Master',
            'jabatan' => 'Jabatan',
            'support-ticket' => 'Kelola User',
            'menu' => 'Menu',
            'submenu' => 'Submenu',
            'subsubmenu' => 'Subsubmenu',
            ];

        // ============================================
        // LOGIC: Convention Over Configuration
        // ============================================
        
        // Jika ada submenu
        if ($submenuSlug) {
            // 1. Check: Ada override untuk slug ini?
            if (isset($submenuPermissionOverrides[$submenuSlug])) {
                return $submenuPermissionOverrides[$submenuSlug];
            }
            
            // 2. Convention: Titleize slug → permission name
            // Example: 'support-ticket' → 'Support Ticket'
            //          'company' → 'Company'
            //          'jabatan' → 'Jabatan'
            return $this->slugToPermissionName($submenuSlug);
        }

        // Return menu-level permission
        return $menuPermissions[$menuSlug] ?? $this->slugToPermissionName($menuSlug);
    }

    /**
     * ✨ NEW: Convert slug to Permission Name (Title Case)
     * 
     * Examples:
     * - 'support-ticket' → 'Support Ticket'
     * - 'user' → 'User'
     * - 'company' → 'Company'
     * - 'herbisida' → 'Herbisida'
     */
    private function slugToPermissionName($slug)
    {
        // Replace hyphens/underscores with spaces
        $name = str_replace(['-', '_'], ' ', $slug);
        
        // Title case
        return ucwords($name);
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