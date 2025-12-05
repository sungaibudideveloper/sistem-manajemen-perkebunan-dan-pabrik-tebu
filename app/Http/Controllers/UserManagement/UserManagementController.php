<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckPermission;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use App\Models\User;
use App\Models\Permission;
use App\Models\Jabatan;
use App\Models\JabatanPermission;
use App\Models\UserPermission;
use App\Models\UserCompany;
use App\Models\Company;
use App\Models\SupportTicket;
use App\Models\UserActivity;
use App\Models\ActivityGroup;

class UserManagementController extends Controller
{
    // =============================================================================
    // USER MANAGEMENT METHODS
    // =============================================================================

    public function userIndex()
    {
        $search = request('search');
        $perPage = request('perPage', 10);
        $companycode = session('companycode'); // Get from session seperti di TenagaKerjaController

        $result = User::with(['jabatan', 'userCompanies', 'userActivities'])
            ->when($search, function ($query, $search) {
                return $query->where('userid', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('companycode', 'like', "%{$search}%")
                    ->orWhereHas('jabatan', function ($q) use ($search) {
                        $q->where('namajabatan', 'like', "%{$search}%");
                    });
            })
            ->orderBy('createdat', 'desc')
            ->paginate($perPage);

        $jabatan = Jabatan::orderBy('namajabatan')->get();
        $companies = Company::orderBy('name')->get(); // Fixed: company.name bukan companyname

        // Activity groups
        $activityGroupOptions = ActivityGroup::orderBy('activitygroup')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->activitygroup,  // Untuk value checkbox/select
                    'label' => $item->activitygroup,  // Untuk display
                    'groupname' => $item->groupname   // Optional: kategori/keterangan
                ];
            });

        $activityGroupLookup = ActivityGroup::pluck('groupname', 'activitygroup')->toArray();
        return view('master.usermanagement.user.index', [
            'title' => 'User Management',
            'navbar' => 'User Management',
            'nav' => 'Users',
            'result' => $result,
            'jabatan' => $jabatan,
            'companies' => $companies,
            'perPage' => $perPage,
            'companycode' => $companycode,
            'activityGroupOptions' => $activityGroupOptions,
            'activityGroupLookup' => $activityGroupLookup
        ]);
    }

    public function userCreate()
    {
        $jabatan = Jabatan::orderBy('namajabatan')->get();
        $companies = Company::orderBy('name')->get();

        return view('master.usermanagement.user.create', [
            'title' => 'Create New User',
            'navbar' => 'User Management',
            'nav' => 'Create User',
            'jabatan' => $jabatan,
            'companies' => $companies
        ]);
    }

    public function userStore(Request $request)
    {

        $request->validate([
            'userid' => 'required|string|max:50|unique:user,userid',
            'name' => 'required|string|max:30',
            'companycode' => 'required|string|max:4|exists:company,companycode',
            'idjabatan' => 'required|integer|exists:jabatan,idjabatan',
            'password' => 'required|string|min:6',
            'isactive' => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            $selected = array_filter(array_map('trim', (array) $request->input('activitygroups', [])));
            $joined = implode(',', $selected); // hasil: "I,II,III"
            // Create user

            $activity = UserActivity::where('userid', $request->userid)
                ->where('companycode', $request->companycode)
                ->first();
            // dd($selected,$joined,$activity);
            if (!$activity) {

                UserActivity::create([
                    'userid' => $request->userid,
                    'companycode' => $request->companycode,
                    'activitygroup' => $joined,
                    'grantedby' => Auth::user()->userid,
                    'createdat' => now(),
                    'updatedat' => null
                ]);
            }

            $user = User::create([
                'userid' => $request->userid,
                'name' => $request->name,
                'companycode' => $request->companycode,
                'idjabatan' => $request->idjabatan,
                'password' => Hash::make($request->password),
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'isactive' => $request->isactive ?? 1,
                'mpassword' => md5($request->password)
            ]);

            // Auto-assign user to primary company
            UserCompany::create([
                'userid' => $request->userid,
                'companycode' => $request->companycode,
                'isactive' => 1,
                'grantedby' => Auth::user()->userid,
                'createdat' => now()
            ]);



            DB::commit();

            return redirect()->route('usermanagement.user.index')
                ->with('success', 'User berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    public function userEdit($userid)
    {
        $user = User::with(['jabatan', 'userCompanies'])->find($userid);

        if (!$user) {
            return redirect()->route('usermanagement.user.index')
                ->with('error', 'User tidak ditemukan');
        }

        $jabatan = Jabatan::orderBy('namajabatan')->get();
        $companies = Company::orderBy('name')->get();

        // Get effective permissions for display
        $effectivePermissions = $this->getUserEffectivePermissions($userid);

        return view('master.usermanagement.user.edit', [
            'title' => 'Edit User: ' . $user->name,
            'navbar' => 'User Management',
            'nav' => 'Edit User',
            'user' => $user,
            'jabatan' => $jabatan,
            'companies' => $companies,
            'effectivePermissions' => $effectivePermissions
        ]);
    }

    public function userUpdate(Request $request, $userid)
    {

        $request->validate([
            'name' => 'required|string|max:30',
            'companycode' => 'required|string|max:4|exists:company,companycode',
            'idjabatan' => 'required|integer|exists:jabatan,idjabatan',
            'isactive' => 'boolean'
        ]);

        try {
            $user = User::find($userid);

            if (!$user) {
                return redirect()->route('usermanagement.user.index')
                    ->with('error', 'User tidak ditemukan');
            }

            // 🔥 CHECK: Apakah jabatan berubah?
            $jabatanChanged = $user->idjabatan != $request->idjabatan;

            $user->update([
                'name' => $request->name,
                'companycode' => $request->companycode,
                'idjabatan' => $request->idjabatan,
                'isactive' => $request->has('isactive') ? 1 : 0,
                'updatedat' => now()
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $request->validate(['password' => 'string|min:6']);
                $user->update([
                    'password' => Hash::make($request->password),
                    'mpassword' => md5($request->password)
                ]);
            }

            if ($jabatanChanged) {
                $this->clearUserCache($user, 'Jabatan changed');
            }

            $selected = array_filter(array_map('trim', (array) $request->input('activitygroups', [])));
            $joined = implode(',', $selected); // hasil: "I,II,III"
            // Create user
            // dd($joined);
            $activity = UserActivity::where('userid', $request->userid)
                ->where('companycode', $request->companycode)
                ->first();
            //  dd($selected,$joined,$activity);
            if ($activity) {
                UserActivity::where('userid', $request->userid)
                    ->where('companycode', $request->companycode)->update([
                        'activitygroup' => $joined,
                        'grantedby' => Auth::user()->userid,
                        'updatedat' => now(),
                    ]);
            }

            return redirect()->route('usermanagement.user.index')
                ->with('success', 'User berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui user: ' . $e->getMessage());
        }
    }

    public function userDestroy($userid)
    {
        try {
            $user = User::find($userid);

            if (!$user) {
                return redirect()->route('usermanagement.user.index')
                    ->with('error', 'User tidak ditemukan');
            }

            // Soft delete by setting isactive = 0
            $user->update([
                'isactive' => 0,
                'updatedat' => now()
            ]);

            return redirect()->route('usermanagement.user.index')
                ->with('success', 'User berhasil dinonaktifkan');
        } catch (\Exception $e) {
            return redirect()->route('usermanagement.user.index')
                ->with('error', 'Gagal menonaktifkan user: ' . $e->getMessage());
        }
    }

    // =============================================================================
    // PERMISSION MASTER DATA METHODS
    // =============================================================================

    public function permissionIndex()
    {
        $search = request('search');
        $perPage = request('perPage', 20);
        $categoryFilter = request('categories') ? explode(',', request('categories')) : [];

        $result = Permission::when($search, function ($query, $search) {
            return $query->where('permissionname', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        })
            ->when(!empty($categoryFilter), function ($query) use ($categoryFilter) {
                return $query->whereIn('category', $categoryFilter);
            })
            ->orderBy('permissionid') // ✅ ADD THIS
            ->paginate($perPage);

        $categories = Permission::distinct()->pluck('category')->filter()->sort();

        return view('master.usermanagement.permissions-masterdata.index', [
            'title' => 'Permission Master Data',
            'navbar' => 'User Management',
            'nav' => 'Permissions',
            'result' => $result,
            'categories' => $categories,
            'perPage' => $perPage
        ]);
    }

    public function permissionStore(Request $request)
    {
        $request->validate([
            'permissionname' => 'required|string|max:100|unique:permissions,permissionname',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'isactive' => 'boolean'
        ]);

        try {
            Permission::create([
                'permissionname' => $request->permissionname,
                'category' => $request->category,
                'description' => $request->description,
                'isactive' => $request->has('isactive') ? 1 : 0
            ]);

            // CLEAR navigation menu cache (karena ada permission baru)
            Cache::forget('navigationMenus');
            Cache::forget('allSubmenus');

            return redirect()->route('usermanagement.permissions-masterdata.index')
                ->with('success', 'Permission berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan permission: ' . $e->getMessage());
        }
    }

    public function permissionUpdate(Request $request, $permissionid)
    {
        $request->validate([
            'permissionname' => 'required|string|max:100|unique:permissions,permissionname,' . $permissionid . ',permissionid',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'isactive' => 'boolean'
        ]);

        try {
            $permission = Permission::find($permissionid);

            if (!$permission) {
                return redirect()->route('usermanagement.permissions-masterdata.index')
                    ->with('error', 'Permission tidak ditemukan');
            }

            // Simpan nama lama untuk tracking
            $oldName = $permission->permissionname;

            $permission->update([
                'permissionname' => $request->permissionname,
                'category' => $request->category,
                'description' => $request->description,
                'isactive' => $request->has('isactive') ? 1 : 0
            ]);

            // CLEAR CACHE jika permission name berubah
            if ($oldName !== $request->permissionname) {
                $this->clearCacheForPermission($oldName);
            }
            
            // Clear menu cache juga (kalau ada menu yang link ke permission ini)
            Cache::forget('navigationMenus');
            Cache::forget('allSubmenus');

            return redirect()->route('usermanagement.permissions-masterdata.index')
                ->with('success', 'Permission berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui permission: ' . $e->getMessage());
        }
    }

    public function permissionDestroy($permissionid)
    {
        try {
            $permission = Permission::find($permissionid);

            if (!$permission) {
                return redirect()->route('usermanagement.permissions-masterdata.index')
                    ->with('error', 'Permission tidak ditemukan');
            }

            // Check if permission is being used
            $usageCount = JabatanPermission::where('permissionid', $permissionid)->where('isactive', 1)->count();
            $userUsageCount = UserPermission::where('permissionid', $permissionid)->where('isactive', 1)->count();

            if ($usageCount > 0 || $userUsageCount > 0) {
                return redirect()->route('usermanagement.permissions-masterdata.index')
                    ->with('error', 'Permission sedang digunakan dan tidak bisa dihapus');
            }

            // CLEAR CACHE sebelum soft delete
            $this->clearCacheForPermission($permission->permissionname);

            // Soft delete by setting isactive = 0
            $permission->update(['isactive' => 0]);
            
            // Clear menu cache
            Cache::forget('navigationMenus');
            Cache::forget('allSubmenus');

            return redirect()->route('usermanagement.permissions-masterdata.index')
                ->with('success', 'Permission berhasil dinonaktifkan');
        } catch (\Exception $e) {
            return redirect()->route('usermanagement.permissions-masterdata.index')
                ->with('error', 'Gagal menonaktifkan permission: ' . $e->getMessage());
        }
    }

    // =============================================================================
    // JABATAN PERMISSION METHODS
    // =============================================================================

    public function jabatanPermissionIndex()
    {
        $search = request('search');
        $perPage = request('perPage', 10);

        $result = Jabatan::withCount(['jabatanPermissions' => function ($query) {
            $query->where('isactive', 1);
        }])
            ->when($search, function ($query, $search) {
                return $query->where('namajabatan', 'like', "%{$search}%");
            })
            ->orderBy('namajabatan')
            ->paginate($perPage);

        $permissions = Permission::where('isactive', 1)
            ->orderBy('category')
            ->orderBy('permissionname')
            ->get()
            ->groupBy('category');

        return view('master.usermanagement.jabatan.index', [
            'title' => 'Jabatan Management',
            'navbar' => 'User Management',
            'nav' => 'Jabatan Permissions',
            'result' => $result,
            'permissions' => $permissions,
            'perPage' => $perPage
        ]);
    }

    public function jabatanPermissionStore(Request $request)
    {
        $request->validate([
            'idjabatan' => 'required|integer|exists:jabatan,idjabatan',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,permissionid'
        ]);

        try {
            DB::beginTransaction();

            Log::info('Permission assignment request:', [
                'idjabatan' => $request->idjabatan,
                'permissions' => $request->permissions ?? []
            ]);

            // STEP 1: Nonaktifkan semua permissions untuk jabatan ini
            JabatanPermission::where('idjabatan', $request->idjabatan)
                ->update(['isactive' => 0]);

            // STEP 2: Aktifkan hanya permissions yang dipilih
            $selectedPermissions = $request->permissions ?? [];

            foreach ($selectedPermissions as $permissionid) {
                JabatanPermission::updateOrCreate([
                    'idjabatan' => $request->idjabatan,
                    'permissionid' => $permissionid
                ], [
                    'isactive' => 1,
                    'grantedby' => Auth::user()->userid,
                    'createdat' => now()
                ]);
            }

            // CLEAR CACHE untuk semua user dalam jabatan ini
            $this->clearCacheForJabatan($request->idjabatan);

            DB::commit();

            return redirect()->route('usermanagement.jabatan.index')
                ->with('success', 'Permissions berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign permissions:', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal memperbarui permissions: ' . $e->getMessage());
        }
    }

    /**
     * Store new jabatan
     */
    public function jabatanStore(Request $request)
    {
        $request->validate([
            'namajabatan' => 'required|string|max:30|unique:jabatan,namajabatan',
        ]);

        try {
            Jabatan::create([
                'namajabatan' => $request->namajabatan,
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
            ]);

            return redirect()->route('usermanagement.jabatan.index')
                ->with('success', 'Jabatan berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Update jabatan
     */
    public function jabatanUpdate(Request $request, $idjabatan)
    {
        $request->validate([
            'namajabatan' => 'required|string|max:30|unique:jabatan,namajabatan,' . $idjabatan . ',idjabatan',
        ]);

        try {
            $jabatan = Jabatan::find($idjabatan);

            if (!$jabatan) {
                return redirect()->route('usermanagement.jabatan.index')
                    ->with('error', 'Jabatan tidak ditemukan');
            }

            $jabatan->update([
                'namajabatan' => $request->namajabatan,
                'updateby' => Auth::user()->userid,
                'updatedat' => now()
            ]);

            $this->clearCacheForJabatan($idjabatan);

            return redirect()->route('usermanagement.jabatan.index')
                ->with('success', 'Jabatan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Delete jabatan (soft delete)
     */
    public function jabatanDestroy($idjabatan)
    {
        try {
            $jabatan = Jabatan::find($idjabatan);

            if (!$jabatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jabatan tidak ditemukan'
                ], 404);
            }

            // Check if jabatan is being used by any users
            $userCount = User::where('idjabatan', $idjabatan)->where('isactive', 1)->count();

            if ($userCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jabatan sedang digunakan oleh ' . $userCount . ' user dan tidak bisa dihapus'
                ], 422);
            }

            // ✅ CLEAR CACHE SEBELUM delete (untuk cleanup)
            $this->clearCacheForJabatan($idjabatan);

            // Check if jabatan has any permissions
            $permissionCount = JabatanPermission::where('idjabatan', $idjabatan)
                ->where('isactive', 1)
                ->count();

            if ($permissionCount > 0) {
                // Deactivate all permissions for this jabatan first
                JabatanPermission::where('idjabatan', $idjabatan)
                    ->update(['isactive' => 0]);
            }

            // Delete the jabatan
            $jabatan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete jabatan:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jabatan: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================================================================
    // USER COMPANY ACCESS METHODS
    // =============================================================================

    public function userCompanyIndex()
    {
        $search = request('search');
        $perPage = request('perPage', 15);

        // Group by user, aggregate companies
        $result = User::with(['jabatan', 'userCompanies' => function ($query) {
            $query->where('isactive', 1);
        }, 'userCompanies.company'])
            ->whereHas('userCompanies', function ($query) {
                $query->where('isactive', 1);
            })
            ->when($search, function ($query, $search) {
                return $query->where('userid', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('userCompanies', function ($q) use ($search) {
                        $q->where('companycode', 'like', "%{$search}%")
                            ->where('isactive', 1);
                    })
                    ->orWhereHas('userCompanies.company', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy('userid')
            ->paginate($perPage);

        // Get users who don't have any company access yet
        $users = User::with('jabatan')
            ->where('isactive', 1)
            ->whereDoesntHave('userCompanies', function ($query) {
                $query->where('isactive', 1);
            })
            ->orderBy('name')
            ->get();

        $companies = Company::orderBy('name')->get();

        return view('master.usermanagement.user-company-permissions.index', [
            'title' => 'User Company Access',
            'navbar' => 'User Management',
            'nav' => 'Company Access',
            'result' => $result,
            'users' => $users,
            'companies' => $companies,
            'perPage' => $perPage
        ]);
    }

    public function userCompanyStore(Request $request)
    {
        $request->validate([
            'userid' => 'required|string|exists:user,userid',
            'companycodes' => 'required|array',
            'companycodes.*' => 'string|exists:company,companycode'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->companycodes as $companycode) {
                UserCompany::updateOrCreate([
                    'userid' => $request->userid,
                    'companycode' => $companycode
                ], [
                    'isactive' => 1,
                    'grantedby' => Auth::user()->userid,
                    'createdat' => now()
                ]);
            }

            // CLEAR CACHE setelah company access berubah
            $user = User::find($request->userid);
            if ($user) {
                $this->clearUserAndCompanyCache($user, 'Company access added');
            }

            DB::commit();

            return redirect()->route('usermanagement.user-company-permissions.index')
                ->with('success', 'Company access berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal menambahkan company access: ' . $e->getMessage());
        }
    }

    public function userCompanyDestroy($userid, $companycode)
    {
        try {
            UserCompany::where('userid', $userid)
                ->where('companycode', $companycode)
                ->update([
                    'isactive' => 0,
                    'updatedat' => now()
                ]);

            // CLEAR CACHE (company access & permission cache)
            $user = User::find($userid);
            if ($user) {
                $this->clearUserAndCompanyCache($user, 'Company access removed');
            }

            return redirect()->route('usermanagement.user-company-permissions.index')
                ->with('success', 'Company access berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('usermanagement.user-company-permissions.index')
                ->with('error', 'Gagal menghapus company access');
        }
    }

    public function userCompanyAssign(Request $request)
    {
        $request->validate([
            'userid' => 'required|string|exists:user,userid',
            'companycodes' => 'array',
            'companycodes.*' => 'string|exists:company,companycode'
        ]);

        try {
            DB::beginTransaction();

            // STEP 1: Nonaktifkan semua company access untuk user ini
            UserCompany::where('userid', $request->userid)
                ->update(['isactive' => 0]);

            // STEP 2: Aktifkan hanya companies yang dipilih
            $selectedCompanies = $request->companycodes ?? [];

            foreach ($selectedCompanies as $companycode) {
                UserCompany::updateOrCreate([
                    'userid' => $request->userid,
                    'companycode' => $companycode
                ], [
                    'isactive' => 1,
                    'grantedby' => Auth::user()->userid,
                    'createdat' => now()
                ]);
            }

            // CLEAR CACHE (company access berubah)
            $user = User::find($request->userid);
            if ($user) {
                $this->clearUserAndCompanyCache($user, 'Company access updated');
            }

            DB::commit();

            return redirect()->route('usermanagement.user-company-permissions.index')
                ->with('success', 'Company access berhasil diperbarui untuk user');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal memperbarui company access: ' . $e->getMessage());
        }
    }

    // =============================================================================
    // USER PERMISSION OVERRIDE METHODS
    // =============================================================================

    public function userPermissionIndex()
    {
        $search = request('search');
        $perPage = request('perPage', 15);

        $result = UserPermission::with(['user.jabatan', 'permissionModel'])
            ->when($search, function ($query, $search) {
                return $query->where('userid', 'like', "%{$search}%")
                    ->orWhere('permission', 'like', "%{$search}%")
                    ->orWhere('companycode', 'like', "%{$search}%");
            })
            ->where('isactive', 1)
            ->orderBy('userid')
            ->orderBy('permission')
            ->paginate($perPage);

        $users = User::with('jabatan')->where('isactive', 1)->orderBy('name')->get();
        $permissions = Permission::where('isactive', 1)
            ->orderBy('category')
            ->orderBy('permissionname')
            ->get()
            ->groupBy('category');
        $companies = Company::orderBy('name')->get();

        return view('master.usermanagement.user-permissions.index', [
            'title' => 'User Permission Overrides',
            'navbar' => 'User Management',
            'nav' => 'Permission Overrides',
            'result' => $result,
            'users' => $users,
            'permissions' => $permissions,
            'companies' => $companies,
            'perPage' => $perPage
        ]);
    }

    public function userPermissionStore(Request $request)
    {
        $request->validate([
            'userid' => 'required|string|exists:user,userid',
            'companycode' => 'required|string|exists:company,companycode',
            'permissionid' => 'required|integer|exists:permissions,permissionid',
            'permissiontype' => 'required|in:GRANT,DENY',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            // Check if user has access to the company
            $userCompany = UserCompany::where('userid', $request->userid)
                ->where('companycode', $request->companycode)
                ->where('isactive', 1)
                ->first();

            if (!$userCompany) {
                return redirect()->back()
                    ->with('error', 'User tidak memiliki akses ke company yang dipilih');
            }

            $permission = Permission::find($request->permissionid);

            UserPermission::updateOrCreate([
                'userid' => $request->userid,
                'companycode' => $request->companycode,
                'permission' => $permission->permissionname
            ], [
                'permissionid' => $request->permissionid,
                'permissiontype' => $request->permissiontype,
                'isactive' => 1,
                'reason' => $request->reason,
                'grantedby' => Auth::user()->userid,
                'createdat' => now()
            ]);

            // ✅ CLEAR CACHE saat permission override ditambahkan
            $user = User::find($request->userid);
            if ($user) {
                $this->clearUserCache($user, 'Permission override created: ' . $permission->permissionname);
            }

            return redirect()->route('usermanagement.user-permissions.index')
                ->with('success', 'Permission override berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan permission override: ' . $e->getMessage());
        }
    }

    public function userPermissionDestroy($userid, $companycode, $permission)
    {
        try {
            UserPermission::where('userid', $userid)
                ->where('companycode', $companycode)
                ->where('permission', $permission)
                ->update([
                    'isactive' => 0,
                    'updatedat' => now()
                ]);

            // CLEAR CACHE saat permission override dihapus
            $user = User::find($userid);
            if ($user) {
                $this->clearUserCache($user, 'Permission override removed: ' . $permission);
            }

            return redirect()->route('usermanagement.user-permissions.index')
                ->with('success', 'Permission override berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('usermanagement.user-permissions.index')
                ->with('error', 'Gagal menghapus permission override');
        }
    }

    // =============================================================================
    // UTILITY METHODS
    // =============================================================================

    public function getUserPermissionsSimple($userid)
    {
        try {
            $user = User::with('jabatan')->find($userid);

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $result = [
                'role' => null,
                'overrides' => []
            ];

            // Get role information
            if ($user->idjabatan && $user->jabatan) {
                $permissionCount = JabatanPermission::where('idjabatan', $user->idjabatan)
                    ->where('isactive', 1)
                    ->count();

                $result['role'] = [
                    'idjabatan' => $user->idjabatan,
                    'namajabatan' => $user->jabatan->namajabatan,
                    'count' => $permissionCount
                ];
            }

            // Get user-specific permission overrides
            $userPermissions = UserPermission::where('userid', $userid)
                ->where('isactive', 1)
                ->orderBy('permission')
                ->get();

            foreach ($userPermissions as $perm) {
                // Check if user has access to the company
                $hasCompanyAccess = UserCompany::where('userid', $userid)
                    ->where('companycode', $perm->companycode)
                    ->where('isactive', 1)
                    ->exists();

                if ($hasCompanyAccess) {
                    $result['overrides'][] = [
                        'permission' => $perm->permission,
                        'companycode' => $perm->companycode,
                        'permissiontype' => $perm->permissiontype,
                        'reason' => $perm->reason,
                        'grantedby' => $perm->grantedby,
                        'createdat' => $perm->createdat ? $perm->createdat->format('Y-m-d H:i:s') : null
                    ];
                }
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error getting user permissions: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load permissions'], 500);
        }
    }

    // API method for AJAX calls
    public function getJabatanPermissions($idjabatan)
    {
        $permissions = JabatanPermission::where('idjabatan', $idjabatan)
            ->where('isactive', 1)
            ->with('permission')
            ->get();

        return response()->json([
            'permissions' => $permissions
        ]);
    }

    // Testing method
    public function testUserPermission($userid, $permission)
    {
        $user = User::find($userid);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Use the CheckPermission middleware method
        $middleware = new \App\Http\Middleware\CheckPermission();
        $hasPermission = method_exists($middleware, 'checkUserPermission')
            ? $middleware->checkUserPermission($user, $permission)
            : false;

        return response()->json([
            'user' => $user->userid,
            'permission' => $permission,
            'has_permission' => $hasPermission,
            'effective_permissions' => $this->getUserEffectivePermissions($userid)
        ]);
    }

    // =============================================================================
    // SUPPORT TICKET METHODS
    // =============================================================================

    public function ticketIndex()
    {
        $search = request('search');
        $perPage = request('perPage', 15);
        $statusFilter = request('status');
        $categoryFilter = request('category');
        $companycode = session('companycode'); // Filter by session company

        $result = SupportTicket::with('company')
            ->where('companycode', $companycode) // FILTER BY COMPANY
            ->when($search, function ($query, $search) {
                return $query->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('fullname', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->when($statusFilter, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($categoryFilter, function ($query, $category) {
                return $query->where('category', $category);
            })
            ->orderBy('createdat', 'desc')
            ->paginate($perPage);

        $companies = Company::orderBy('name')->get();

        // Get statistics - filtered by company
        $stats = [
            'open' => SupportTicket::where('companycode', $companycode)->where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('companycode', $companycode)->where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('companycode', $companycode)->where('status', 'resolved')->count(),
            'total' => SupportTicket::where('companycode', $companycode)->count(),
        ];

        return view('master.usermanagement.support-ticket.index', [
            'title' => 'Support Tickets',
            'navbar' => 'User Management',
            'nav' => 'Support Tickets',
            'result' => $result,
            'companies' => $companies,
            'stats' => $stats,
            'perPage' => $perPage,
            'companycode' => $companycode
        ]);
    }

    /**
     * Store support ticket dengan full protection
     * - reCAPTCHA v2 verification
     * - Rate limiting (3 req/hour)
     * - Duplicate prevention
     * - Username validation
     */
    public function ticketStore(Request $request)
    {
        // ========================================
        // 1. VALIDATION
        // ========================================
        $validated = $request->validate([
            'fullname' => 'required|string|max:100',
            'username' => 'required|string|max:50',
            'companycode' => 'required|string|max:4|exists:company,companycode',
            'category' => 'required|in:forgot_password,bug_report,support,other',
            'description' => 'nullable|string|max:1000',
            'g-recaptcha-response' => 'required',
        ], [
            'fullname.required' => 'Full name is required',
            'fullname.max' => 'Full name cannot exceed 100 characters',
            'username.required' => 'Username is required',
            'username.max' => 'Username cannot exceed 50 characters',
            'companycode.required' => 'Company is required',
            'companycode.exists' => 'Selected company does not exist',
            'category.required' => 'Category is required',
            'description.max' => 'Description cannot exceed 1000 characters',
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification',
        ]);

        // ========================================
        // 3. RATE LIMITING (Backend)
        // ========================================
        $key = 'support-ticket:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return back()->withErrors([
                'error' => "Too many ticket submissions. Please try again in {$minutes} minute(s)."
            ])->withInput();
        }

        // Increment rate limiter (decay after 1 hour)
        RateLimiter::hit($key, 3600);

        // ========================================
        // 4. CHECK DUPLICATE SUBMISSIONS
        // ========================================
        $recentTicket = \App\Models\SupportTicket::where('username', $validated['username'])
            ->where('category', $validated['category'])
            ->where('status', 'open')
            ->where('createdat', '>', now()->subHours(24))
            ->first();

        if ($recentTicket) {
            return back()->withErrors([
                'error' => 'You already have a pending ticket for this issue. Please wait for admin response. Ticket Number: ' . $recentTicket->ticket_number
            ])->withInput();
        }

        // ========================================
        // 5. VERIFY USERNAME EXISTS
        // ========================================
        $userExists = DB::table('user')
            ->where('userid', $validated['username'])
            ->where('companycode', $validated['companycode'])
            ->exists();

        if (!$userExists) {
            return back()->withErrors([
                'error' => 'Username not found in the selected company. Please verify your information.'
            ])->withInput();
        }

        // ========================================
        // 6. CREATE TICKET
        // ========================================
        try {
            DB::beginTransaction();

            $ticketNumber = \App\Models\SupportTicket::generateTicketNumber($validated['companycode']);

            $ticket = \App\Models\SupportTicket::create([
                'ticket_number' => $ticketNumber,
                'category' => $validated['category'],
                'status' => 'open',
                'priority' => 'medium',
                'fullname' => $validated['fullname'],
                'username' => $validated['username'],
                'companycode' => $validated['companycode'],
                'description' => $validated['description'] ?? null
            ]);

            // Create notification for admin
            \App\Http\Controllers\NotificationController::notifyNewSupportTicket($ticket);

            // Log activity
            Log::info('Support ticket created', [
                'ticket_number' => $ticketNumber,
                'category' => $validated['category'],
                'username' => $validated['username'],
                'ip' => $request->ip(),
            ]);

            DB::commit();

            return redirect()->route('login')->with(
                'success',
                'Your request has been submitted successfully. Our admin team will contact you soon. Ticket Number: ' . $ticketNumber
            );
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create support ticket', [
                'error' => $e->getMessage(),
                'username' => $validated['username'],
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'error' => 'Failed to submit ticket. Please try again later or contact IT support directly.'
            ])->withInput();
        }
    }

    /**
     * Verify Google reCAPTCHA v2 response using Guzzle HTTP
     *
     * @param string $response - g-recaptcha-response token
     * @param string $ipAddress - User IP address
     * @return bool
     */
    private function verifyRecaptcha($response, $ipAddress)
    {
        if (empty($response)) {
            return false;
        }

        try {
            $secretKey = config('services.recaptcha.secret_key');

            // Send verification request to Google
            $verifyResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $response,
                'remoteip' => $ipAddress
            ]);

            $result = $verifyResponse->json();

            // Optional: Log for debugging
            if (!($result['success'] ?? false)) {
                Log::warning('reCAPTCHA verification failed', [
                    'ip' => $ipAddress,
                    'error_codes' => $result['error-codes'] ?? [],
                ]);
            }

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', [
                'error' => $e->getMessage(),
                'ip' => $ipAddress,
            ]);

            // PRODUCTION: return false (strict)
            // DEVELOPMENT: return true (bypass jika Google down)
            return config('app.env') === 'local';
        }
    }

    public function ticketUpdate(Request $request, $ticket_id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|in:low,medium,high',
            'resolution_notes' => 'nullable|string'
        ]);

        try {
            $ticket = SupportTicket::findOrFail($ticket_id);

            $updateData = [
                'status' => $request->status,
                'priority' => $request->priority ?? $ticket->priority
            ];

            if ($request->filled('resolution_notes')) {
                $updateData['resolution_notes'] = $request->resolution_notes;
            }

            // Track in_progress status change
            if ($request->status === 'in_progress' && $ticket->status !== 'in_progress') {
                $updateData['inprogress_by'] = Auth::user()->userid;
                $updateData['inprogress_at'] = now();
            }

            // Track resolved/closed status change
            if (
                in_array($request->status, ['resolved', 'closed']) &&
                !in_array($ticket->status, ['resolved', 'closed'])
            ) {
                $updateData['resolved_by'] = Auth::user()->userid;
                $updateData['resolved_at'] = now();
            }

            $ticket->update($updateData);

            return redirect()->back()
                ->with('success', 'Ticket berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Failed to update ticket:', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal memperbarui ticket: ' . $e->getMessage());
        }
    }

    public function ticketDestroy($ticket_id)
    {
        try {
            $ticket = SupportTicket::findOrFail($ticket_id);
            $ticket->delete();

            return redirect()->back()
                ->with('success', 'Ticket berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus ticket: ' . $e->getMessage());
        }
    }

    // =============================================================================
    // USER ACTIVITY PERMISSION FUNCTIONS 
    // =============================================================================
    public function UserActivityPermission(Request $request)
    {
        $search = request('search');
        $perPage = request('perPage', 15);
        $companycode = session('companycode');

        // Query untuk user yang SUDAH punya activity (untuk tabel)
        $result = UserActivity::with(['user', 'company', 'activityGroup'])
            ->where('companycode', $companycode)
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('userid', 'like', "%{$search}%")
                        ->orWhere('activitygroup', 'like', "%{$search}%");
                });
            })
            ->orderBy('createdat', 'desc')
            ->paginate($perPage);

        $companies = Company::orderBy('name')->get();

        $users = User::where('isactive', 1)
            ->whereHas('userCompanies', function ($q) use ($companycode) {
                $q->where('companycode', $companycode)
                    ->where('isactive', 1);
            })
            ->orderBy('name')
            ->get();

        // Activity groups
        $activityGroupOptions = ActivityGroup::orderBy('activitygroup')
            ->pluck('activitygroup', 'groupname');
        // dd($activityGroupOptions);
        return view('master.usermanagement.user-activity-permission.index', [
            'title' => 'User Activity Permission',
            'navbar' => 'User Management',
            'nav' => 'User Activity Permission',
            'result' => $result,
            'companies' => $companies,
            'users' => $users,
            'perPage' => $perPage,
            'companycode' => $companycode,
            'activitygroup' => $activityGroupOptions
        ]);
    }
    public function userActivityAssign(Request $request)
    {
        try {
            DB::beginTransaction();

            // Cek akses user ke company
            $userCompany = UserCompany::where('userid', $request->userid)
                ->where('companycode', $request->companycode)
                ->where('isactive', 1)
                ->first();

            if (!$userCompany) {
                return back()->with('error', 'User tidak memiliki akses ke company yang dipilih');
            }

            $selected = array_filter(array_map('trim', (array) $request->input('activitygroups', [])));
            $joined = implode(',', $selected); // hasil: "I,II,III"

            $activity = UserActivity::where('userid', $request->userid)
                ->where('companycode', $request->companycode)
                ->first();

            if ($activity) {
                UserActivity::where('userid', $request->userid)
                    ->where('companycode', $request->companycode)->update([
                        'activitygroup' => $joined,
                        'grantedby' => Auth::user()->userid,
                        'updatedat' => now(),
                    ]);
            } else {
                UserActivity::create([
                    'userid' => $request->userid,
                    'companycode' => $request->companycode,
                    'activitygroup' => $joined,
                    'grantedby' => Auth::user()->userid,
                    'createdat' => now(),
                    'updatedat' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('usermanagement.user-activity-permission.index')
                ->with('success', 'Activity groups berhasil diperbarui untuk user');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign activity groups:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal memperbarui activity groups: ' . $e->getMessage());
        }
    }

    public function getUserActivities($userid, $companycode)
    {
        try {
            // Return comma-separated string dalam array
            $activity = UserActivity::where('userid', $userid)
                ->where('companycode', $companycode)
                ->first();

            $activities = $activity ? [$activity->activitygroup] : [];

            return response()->json([
                'success' => true,
                'activities' => $activities // ["I,II,III"]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load user activities'
            ], 500);
        }
    }

    /**
     * Remove specific activity from user
     */
    public function userActivityDestroy($userid, $companycode, $activitygroup)
    {
        try {
            UserActivity::where('userid', $userid)
                ->where('companycode', $companycode)
                ->where('activitygroup', $activitygroup)
                ->delete();


            return redirect()->back()
                ->with('success', 'Activity group berhasil dihapus dari user');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus activity group');
        }
    }

    /**
     * Get user activities untuk current company session
     */
    public function getUserActivitiesForCurrentCompany($userid, $companycode = null)
    {
        try {
            $companycode = $companycode ?? session('companycode');

            $activities = UserActivity::where('userid', $userid)
                ->where('companycode', $companycode)
                ->where('isactive', 1)
                ->pluck('activitygroup')
                ->toArray();

            return response()->json([
                'success' => true,
                'activities' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load activities'
            ], 500);
        }
    }



    // =============================================================================
    // PRIVATE HELPER FUNCTIONS - CACHE MANAGEMENT
    // =============================================================================

    /**
     * Clear permission cache untuk single user
     *
     * @param User $user
     * @param string $reason - Reason untuk logging
     * @return void
     */
    private function clearUserCache(User $user, string $reason = 'Manual clear')
    {
        // Clear permission cache
        CheckPermission::clearUserCache($user);

        \App\View\Composers\NavigationComposer::clearNavigationCache($user);

        Log::info('Permission & navigation cache cleared', [
            'userid' => $user->userid,
            'jabatan' => $user->idjabatan,
            'reason' => $reason
        ]);
    }

    /**
     * Clear cache user + company cache sekaligus
     * Dipakai saat company access berubah
     *
     * @param User $user
     * @param string $reason
     * @return void
     */
    private function clearUserAndCompanyCache(User $user, string $reason = 'Company access changed')
    {
        // Clear permission cache
        CheckPermission::clearUserCache($user);

        // Clear company cache
        $cacheKey = "user_companies_{$user->userid}";
        \Cache::forget($cacheKey);

        \App\View\Composers\NavigationComposer::clearNavigationCache($user);

        Log::info('Permission, company & navigation cache cleared', [
            'userid' => $user->userid,
            'reason' => $reason
        ]);
    }

    /**
     * Clear cache untuk semua user dalam jabatan tertentu
     * Dipakai saat jabatan permission berubah (mass update)
     *
     * @param int $idjabatan
     * @return int - Jumlah user yang di-clear cache-nya
     */
    private function clearCacheForJabatan($idjabatan)
    {
        $users = User::where('idjabatan', $idjabatan)
            ->where('isactive', 1)
            ->get();

        foreach ($users as $user) {
            CheckPermission::clearUserCache($user);

            // Clear navigation cache per user
            \App\View\Composers\NavigationComposer::clearNavigationCache($user);
        }

        Log::info('Bulk cache clear (permissions + navigation) for jabatan', [
            'idjabatan' => $idjabatan,
            'affected_users' => $users->count()
        ]);

        return $users->count();
    }

    /**
     * Clear cache untuk multiple users sekaligus
     * Berguna untuk bulk operations
     *
     * @param array $userIds
     * @return int - Jumlah user yang di-clear cache-nya
     */
    private function clearCacheForUsers(array $userIds)
    {
        $users = User::whereIn('userid', $userIds)
            ->where('isactive', 1)
            ->get();

        foreach ($users as $user) {
            CheckPermission::clearUserCache($user);
        }

        Log::info('Bulk cache clear for users', [
            'count' => $users->count(),
            'userids' => $userIds
        ]);

        return $users->count();
    }

    /**
     * Clear cache untuk semua user yang terpengaruh oleh permission tertentu
     * Dipakai saat permission name berubah atau dihapus
     *
     * @param string $permissionName
     * @return int - Jumlah user yang di-clear cache-nya
     */
    private function clearCacheForPermission($permissionName)
    {
        try {
            // Get permission ID
            $permission = Permission::where('permissionname', $permissionName)->first();
            
            if (!$permission) {
                Log::warning('Permission not found for cache clear', ['permission' => $permissionName]);
                return 0;
            }

            // Find all jabatan yang pakai permission ini
            $jabatanIds = JabatanPermission::where('permissionid', $permission->permissionid)
                ->where('isactive', 1)
                ->pluck('idjabatan')
                ->unique();

            // Clear cache untuk semua user dalam jabatan tersebut
            $affectedCount = 0;
            foreach ($jabatanIds as $idjabatan) {
                $affectedCount += $this->clearCacheForJabatan($idjabatan);
            }

            // Also clear untuk user dengan permission override
            $userIds = UserPermission::where('permission', $permissionName)
                ->where('isactive', 1)
                ->pluck('userid')
                ->unique()
                ->toArray();

            if (!empty($userIds)) {
                $affectedCount += $this->clearCacheForUsers($userIds);
            }

            Log::info('Bulk cache clear for permission', [
                'permission' => $permissionName,
                'affected_jabatan' => $jabatanIds->count(),
                'affected_users' => $affectedCount
            ]);

            return $affectedCount;
        } catch (\Exception $e) {
            Log::error('Error clearing cache for permission', [
                'permission' => $permissionName,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
