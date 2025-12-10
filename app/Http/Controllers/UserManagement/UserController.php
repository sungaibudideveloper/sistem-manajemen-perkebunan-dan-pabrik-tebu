<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserManagement\UserService;
use App\Models\{Jabatan, Company, ActivityGroup};

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display user listing
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'companycode' => session('companycode'),
            'isactive' => 1
        ];

        $perPage = $request->get('perPage', 10);
        $users = $this->userService->getPaginatedUsers($filters, $perPage);

        $jabatan = Jabatan::orderBy('namajabatan')->get();
        $companies = Company::orderBy('name')->get();

        $activityGroupOptions = ActivityGroup::orderBy('activitygroup')
            ->get()
            ->map(fn($item) => [
                'value' => $item->activitygroup,
                'label' => $item->activitygroup,
                'groupname' => $item->groupname
            ]);

        $activityGroupLookup = ActivityGroup::pluck('groupname', 'activitygroup')->toArray();

        return view('usermanagement.user.index', [
            'title' => 'User Management',
            'navbar' => 'User Management',
            'nav' => 'Users',
            'result' => $users,
            'jabatan' => $jabatan,
            'companies' => $companies,
            'perPage' => $perPage,
            'companycode' => session('companycode'),
            'activityGroupOptions' => $activityGroupOptions,
            'activityGroupLookup' => $activityGroupLookup
        ]);
    }

    /**
     * Show user details
     */
    public function show($userid)
    {
        $user = $this->userService->getUserWithRelations($userid, ['jabatan', 'userCompanies', 'userActivities']);

        if (!$user) {
            return redirect()->route('usermanagement.user.index')
                ->with('error', 'User tidak ditemukan');
        }

        return view('usermanagement.user.show', [
            'title' => 'User Detail',
            'navbar' => 'User Management',
            'nav' => 'User Detail',
            'user' => $user
        ]);
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'userid' => 'required|string|max:50|unique:user,userid',
            'name' => 'required|string|max:30',
            'companycode' => 'required|string|max:4|exists:company,companycode',
            'idjabatan' => 'required|integer|exists:jabatan,idjabatan',
            'password' => 'required|string|min:6',
            'isactive' => 'boolean',
            'activitygroups' => 'array',
            'activitygroups.*' => 'string'
        ]);

        $result = $this->userService->createUser($validated, auth()->user()->userid);

        if ($result['success']) {
            return redirect()->route('usermanagement.user.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Update user
     */
    public function update(Request $request, $userid)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30',
            'companycode' => 'required|string|max:4|exists:company,companycode',
            'idjabatan' => 'required|integer|exists:jabatan,idjabatan',
            'isactive' => 'boolean',
            'password' => 'nullable|string|min:6',
            'activitygroups' => 'array',
            'activitygroups.*' => 'string'
        ]);

        $result = $this->userService->updateUser($userid, $validated, auth()->user()->userid);

        if ($result['success']) {
            return redirect()->route('usermanagement.user.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Soft delete user
     */
    public function destroy($userid)
    {
        $result = $this->userService->deactivateUser($userid, auth()->user()->userid);

        return redirect()->route('usermanagement.user.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * AJAX: Get user permissions
     */
    public function getPermissions($userid)
    {
        $result = $this->userService->getUserPermissionsSummary($userid);
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 404);
        }

        return response()->json($result);
    }

    /**
     * AJAX: Get user companies
     */
    public function getCompanies($userid)
    {
        try {
            $user = $this->userService->getUserWithRelations($userid, ['jabatan', 'userCompanies.company']);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Format companies as key-value pairs: companycode => name
            $companies = $user->userCompanies
                ->where('isactive', 1)
                ->filter(fn($uc) => $uc->company !== null)
                ->mapWithKeys(fn($uc) => [
                    $uc->companycode => $uc->company->name
                ])
                ->toArray();

            return response()->json([
                'success' => true,
                'user' => [
                    'userid' => $user->userid,
                    'name' => $user->name,
                    'jabatan' => $user->jabatan ? [
                        'idjabatan' => $user->jabatan->idjabatan,
                        'namajabatan' => $user->jabatan->namajabatan
                    ] : null
                ],
                'companies' => $companies
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching user companies', [
                'userid' => $userid,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data'
            ], 500);
        }
    }

    /**
     * AJAX: Get user activities
     */
    public function getActivities($userid)
    {
        $companycode = session('companycode');
        $activities = $this->userService->getUserActivities($userid, $companycode);

        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }
}