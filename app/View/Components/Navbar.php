<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Menu;
use App\Models\Submenu;
use App\Models\Subsubmenu;
use App\Models\User;
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
            // Eager load secara manual hingga kedalaman yang Anda inginkan
            return Menu::with([
                'submenus.children.children.children' // Ini memuat hingga 5 level (Menu -> L1 -> L2 -> L3 -> L4)
            ])->orderBy('menuid')->get();
        });

        // ... sisa constructor
    }

    public function render(): View|Closure|string
    {
        // Get user permissions
        $userPermissions = json_decode(auth()->user()->permissions ?? '[]', true);

        // Get semua data
        $allMenus = Menu::orderBy('menuid')->get();
        $allSubmenus = Submenu::orderBy('submenuid')->get();

        // Filter menus yang user punya permission
        $allowedMenus = $allMenus->whereIn('name', $userPermissions);

        // Filter submenu yang user punya permission
        $allowedSubmenus = $allSubmenus->whereIn('name', $userPermissions);

        // Get menu IDs dari submenu yang allowed
        $menuIdsFromSubmenus = $allowedSubmenus->pluck('menuid')->unique();

        // Get menu IDs dari menu yang directly allowed
        $menuIdsFromMenus = $allowedMenus->pluck('menuid');

        // Combine menu IDs (menu yang punya permission langsung ATAU punya submenu dengan permission)
        $finalMenuIds = $menuIdsFromMenus->merge($menuIdsFromSubmenus)->unique();

        // Get final menus
        $menus = $allMenus->whereIn('menuid', $finalMenuIds);

        $this->companyName = auth()->user()->companycode ?? 'Default Company';

        return view('components.navbar', [
            'navigationMenus' => $menus,
            'allSubmenus' => $allSubmenus, // Semua submenu untuk structure
            'userPermissions' => $userPermissions,
            'companyName' => $this->companyName,
        ]);
    }
}
