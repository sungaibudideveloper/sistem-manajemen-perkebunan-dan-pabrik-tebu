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
        $userPermissions = json_decode(auth()->user()->permissions ?? '[]', true);

        $allMenus = Menu::orderBy('menuid')->get();
        $allSubmenus = Submenu::orderBy('submenuid')->get();

        $allowedMenus = $allMenus->whereIn('name', $userPermissions);
        $allowedSubmenus = $allSubmenus->whereIn('name', $userPermissions);

        $menuIdsFromSubmenus = $allowedSubmenus->pluck('menuid')->unique();
        $menuIdsFromMenus = $allowedMenus->pluck('menuid');

        $finalMenuIds = $menuIdsFromMenus->merge($menuIdsFromSubmenus)->unique();

        $menus = $allMenus->whereIn('menuid', $finalMenuIds);

        $this->companyName = auth()->user()->companycode ?? 'Default Company';

        return view('components.navbar', [
            'navigationMenus' => $menus,
            'allSubmenus' => $allSubmenus, 
            'userPermissions' => $userPermissions,
            'companyName' => $this->companyName,
        ]);
    }
}
