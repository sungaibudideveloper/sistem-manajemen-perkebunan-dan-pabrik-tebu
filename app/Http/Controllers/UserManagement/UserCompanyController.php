<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserManagement\UserCompanyService;
use App\Models\Company;

class UserCompanyController extends Controller
{
    protected UserCompanyService $userCompanyService;

    public function __construct(UserCompanyService $userCompanyService)
    {
        $this->userCompanyService = $userCompanyService;
    }

    /**
     * Display user company access listing
     */
    public function index(Request $request)
    {
        $filters = ['search' => $request->get('search')];
        $perPage = $request->get('perPage', 15);

        $result = $this->userCompanyService->getPaginatedUserCompanies($filters, $perPage);
        $users = $this->userCompanyService->getUsersWithoutAccess();
        $companies = Company::orderBy('name')->get();

        return view('usermanagement.usercompany.index', [
            'title' => 'User Company Access',
            'navbar' => 'User Management',
            'nav' => 'Company Access',
            'result' => $result,
            'users' => $users,
            'companies' => $companies,
            'perPage' => $perPage
        ]);
    }

    // No show() method - detail handled in modal

    /**
     * Assign companies to user (add new)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'userid' => 'required|string|exists:user,userid',
            'companycodes' => 'required|array',
            'companycodes.*' => 'string|exists:company,companycode'
        ]);

        $result = $this->userCompanyService->assignCompanies(
            $validated['userid'],
            $validated['companycodes'],
            auth()->user()->userid
        );

        return redirect()->route('usermanagement.usercompany.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Update user company access (replace all)
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'userid' => 'required|string|exists:user,userid',
            'companycodes' => 'array',
            'companycodes.*' => 'string|exists:company,companycode'
        ]);

        $companycodes = $validated['companycodes'] ?? [];
        
        $result = $this->userCompanyService->updateUserCompanies(
            $validated['userid'],
            $companycodes,
            auth()->user()->userid
        );

        return redirect()->route('usermanagement.usercompany.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Remove company access from user
     */
    public function destroy($userid, $companycode)
    {
        $result = $this->userCompanyService->removeCompanyAccess($userid, $companycode);

        return redirect()->route('usermanagement.usercompany.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}