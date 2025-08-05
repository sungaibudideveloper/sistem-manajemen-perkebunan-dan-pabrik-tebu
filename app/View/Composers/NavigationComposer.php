<?php

namespace App\View\Composers;

use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
                'company' => $this->getUserCompanies(), // Tambahkan alias company
                'period' => $this->getMonitoringPeriod() // Tambahkan period
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
     * Get user permissions
     */
    private function getUserPermissions()
    {
        try {
            return DB::table('submenu')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->pluck('name')
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Error getting user permissions: ' . $e->getMessage());
            return [];
        }
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
                
                // Set ke session agar bisa dipanggil di header
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
                    // Format period menjadi lebih readable
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
     * Get user companies
     */
    private function getUserCompanies()
    {
        try {
            if (Auth::check()) {
                $companyRaw = DB::table('usercompany')
                    ->where('userid', Auth::user()->userid)
                    ->value('companycode');
                
                if ($companyRaw) {
                    $companies = explode(',', $companyRaw);
                    sort($companies);
                    return $companies;
                }
            }
            return [];
        } catch (\Exception $e) {
            \Log::error('Error getting user companies: ' . $e->getMessage());
            return [];
        }
    }
}