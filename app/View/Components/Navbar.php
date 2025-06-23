<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Menu;
use App\Models\Submenu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Navbar extends Component
{
    public $navigationMenus;
    public $companyName;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->navigationMenus = Cache::remember('navigation_menus_manual_depth', 3600, function () {
            // Debug logging untuk dashboard menu
            if ($menu->slug === 'dashboard') {
                \Log::info('Dashboard menu check', [
                    'menu' => $menu->name,
                    'submenus_count' => $menu->submenus->count(),
                    'submenus' => $menu->submenus->map(function($s) {
                        return ['name' => $s->name, 'slug' => $s->slug];
                    })
                ]);
            }
            
            // Eager load secara manual hingga kedalaman yang Anda inginkan
            return Menu::with([
                'submenus.children.children.children' // Ini memuat hingga 5 level (Menu -> L1 -> L2 -> L3 -> L4)
            ])->orderBy('menuid')->get();
        });

        // ... sisa constructor
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission)
    {
        if (!$permission) return true;
        
        $userPermissions = json_decode(auth()->user()->permissions ?? '[]');
        
        // Direct permission check
        if (in_array($permission, $userPermissions)) {
            return true;
        }
        
        // Special handling for Dashboard submenu
        // If looking for "Dashboard Agronomi", check if user has both "Dashboard" and "Agronomi"
        if (str_contains($permission, 'Dashboard ')) {
            $parts = explode(' ', $permission);
            if (count($parts) == 2) {
                $hasDashboard = in_array('Dashboard', $userPermissions);
                $hasSubPermission = in_array($parts[1], $userPermissions);
                return $hasDashboard && $hasSubPermission;
            }
        }
        
        return false;
    }

    /**
     * Generate route name from menu and submenu
     */
    public function generateRoute($menu, $submenu)
    {
        // Special cases
        $specialRoutes = [
            // Process routes
            'posting' => 'process.posting',
            'unposting' => 'process.unposting',
            'closing' => 'closing',
            'upload-gpx' => 'upload.gpx.view',
            'export-kml' => 'export.kml.view',
            // Input routes
            'kerja-harian' => 'input.kerjaharian.rencanakerjaharian.index',
            // Master routes
            'master-list' => 'masterdata.master-list.index',
            // Dashboard routes
            'agronomi-dashboard' => 'dashboard.agronomi',
            'hpt-dashboard' => 'dashboard.hpt',
        ];

        if (isset($specialRoutes[$submenu->slug])) {
            return $specialRoutes[$submenu->slug];
        }

        // Route prefix mapping
        $menuPrefixMap = [
            'master' => 'master',
            'input-data' => 'input',
            'report' => 'report',
            'dashboard' => 'dashboard',
            'process' => 'process',
        ];

        // Submenu yang pakai masterdata prefix
        $masterDataSubmenus = ['herbisida', 'herbisida-dosage', 'varietas', 'mandor', 'tenagakerja', 'jabatan', 'accounting', 'kategori', 'approval'];
        $aplikasiSubmenus = ['menu', 'submenu', 'subsubmenu'];

        $prefix = $menuPrefixMap[$menu->slug] ?? $menu->slug;

        // Check if submenu needs masterdata prefix
        if ($menu->slug === 'master' && in_array($submenu->slug, $masterDataSubmenus)) {
            return "masterdata.{$submenu->slug}.index";
        }

        // Check if submenu needs aplikasi prefix
        if ($menu->slug === 'master' && in_array($submenu->slug, $aplikasiSubmenus)) {
            $cleanSlug = str_replace('-app', '', $submenu->slug);
            return "aplikasi.{$cleanSlug}.index";
        }

        // Dashboard routes - remove '-dashboard' suffix from slug
        if ($menu->slug === 'dashboard') {
            $cleanSlug = str_replace('-dashboard', '', $submenu->slug);
            return "{$prefix}.{$cleanSlug}";
        }
        
        // Process routes special handling
        if ($menu->slug === 'process') {
            // Most process routes use process.submenu pattern
            return "{$prefix}.{$submenu->slug}";
        }

        // Default pattern: prefix.submenu.index
        return "{$prefix}.{$submenu->slug}.index";
    }

    /**
     * Get URL from route name
     */
    public function getUrl($routeName)
    {
        try {
            return route($routeName);
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::warning("Route not found: {$routeName}");
            return '#';
        }
    }

    /**
     * Check if menu/submenu is active
     */
    public function isActive($menu, $submenu = null)
    {
        if ($submenu) {
            // Check if menu/submenu is active
            $slug = $submenu->slug;

            // Special cases
            if ($slug === 'kelola-user' && (request()->is('username*') || request()->routeIs('master.username.*'))) {
                return true;
            }

            if ($slug === 'master-list' && request()->is('master-list*')) {
                return true;
            }
            
            // Add dashboard active check
            if ($slug === 'agronomi-dashboard' && request()->routeIs('dashboard.agronomi')) {
                return true;
            }
            
            if ($slug === 'hpt-dashboard' && request()->routeIs('dashboard.hpt')) {
                return true;
            }
            
            // Process menu special cases
            if ($slug === 'unposting' && request()->is('unposting*')) {
                return true;
            }
            
            if ($slug === 'closing' && request()->is('process/closing*')) {
                return true;
            }

            return request()->is($slug . '*') ||
                request()->routeIs($slug . '.*') ||
                request()->routeIs('*.' . $slug . '.*') ||
                request()->is('*/' . $slug);
        }

        // Check menu level
        $activePatterns = [
            'master' => ['company*', 'blok*', 'plotting*', 'mapping*', 'herbisida*', 'jabatan*', 'approval*', 'kategori*', 'varietas*', 'accounting*', 'username*', 'master/*', 'masterdata/*', 'aplikasi/*', 'master-list*', 'mandor*', 'tenagakerja*', 'aktivitas*'],
            'input-data' => ['agronomi*', 'hpt*', 'gudang*', 'input/*', 'kerjaharian/*'],
            'report' => ['*report*', 'report/*'],
            'dashboard' => ['*dashboard*', 'dashboard/*'],
            'process' => ['closing*', 'uploadgpx*', 'exportkml*', 'posting*', 'unposting*', 'process/*'],
        ];

        $patterns = $activePatterns[$menu->slug] ?? [];
        foreach ($patterns as $pattern) {
            if (request()->is($pattern)) return true;
        }
        return false;
    }

    /**
     * Get permission from submenu
     */
    public function getPermission($menu, $submenu = null)
    {
        // Permission mapping
        $permissionMap = [
            // Menu level
            'master' => 'Master',
            'input-data' => 'Input Data',
            'report' => 'Report',
            'dashboard' => 'Dashboard',
            'process' => 'Process',

            // Submenu level
            'company' => 'Company',
            'blok' => 'Blok',
            'plotting' => 'Plotting',
            'mapping' => 'Mapping',
            'herbisida' => 'Herbisida',
            'herbisida-dosage' => 'Dosis Herbisida',
            'varietas' => 'Varietas',
            'jabatan' => 'Jabatan',
            'kategori' => 'Kategori',
            'approval' => 'Approval',
            'accounting' => 'Accounting',
            'kelola-user' => 'Kelola User',
            'agronomi' => 'Agronomi',
            'menu' => 'Menu',
            'submenu' => 'Submenu',
            'subsubmenu' => 'Subsubmenu',

            'hpt' => 'HPT',
            'rkh' => 'HPT',
            'gudang' => 'Gudang',
            'agronomi-report' => 'Report Agronomi',
            'hpt-report' => 'Report HPT',
            'agronomi-dashboard' => 'Agronomi',  // Sesuaikan dengan permission yang ada
            'hpt-dashboard' => 'HPT',  // Sesuaikan dengan permission yang ada
            'posting' => 'Posting',
            'unposting' => 'Unposting',
            'upload-gpx' => 'Upload GPX File',
            'export-kml' => 'Export KML File',
            'closing' => 'Closing',
        ];

        if ($submenu) {
            $permission = $permissionMap[$submenu->slug] ?? null;
            // Debug logging
            \Log::info('Checking permission for submenu', [
                'slug' => $submenu->slug,
                'permission' => $permission,
                'user_permissions' => json_decode(auth()->user()->permissions ?? '[]'),
                'has_permission' => $this->hasPermission($permission)
            ]);
            return $permission;
        }

        $permission = $permissionMap[$menu->slug] ?? null;
        // Debug logging
        \Log::info('Checking permission for menu', [
            'slug' => $menu->slug,
            'permission' => $permission,
            'user_permissions' => json_decode(auth()->user()->permissions ?? '[]'),
            'has_permission' => $this->hasPermission($permission)
        ]);
        return $permission;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.navbar');
    }
}