<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Permission;
use App\Models\JabatanPermission;
use App\Models\UserPermission;
use App\Models\UserCompany;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (!Auth::check()) {
            abort(401, 'Unauthenticated');
        }
        
        $user = Auth::user();
        $hasPermission = $this->checkUserPermission($user, $permission);
        
        if (!$hasPermission) {
            Log::warning('Permission denied', [
                'user' => $user->name,
                'userid' => $user->userid,
                'required_permission' => $permission,
                'user_jabatan' => $user->idjabatan,
                'session_company' => session('companycode')
            ]);
            
            abort(403, 'Unauthorized action. Required permission: ' . $permission);
        }
        
        return $next($request);
    }

    /**
     * Check if user has the required permission using new permission system only
     */
    private function checkUserPermission(User $user, string $permissionName): bool
    {
        // Step 1: Check if permission exists in master data
        $permissionModel = Permission::where('permissionname', $permissionName)
                                   ->where('isactive', 1)
                                   ->first();
        
        if (!$permissionModel) {
            Log::warning('Permission not found in master data', [
                'permission' => $permissionName,
                'userid' => $user->userid
            ]);
            return false;
        }

        // Step 2: Check user-specific permission overrides first (GRANT/DENY)
        $userPermission = UserPermission::where('userid', $user->userid)
                                       ->where('permission', $permissionName)
                                       ->where('isactive', 1)
                                       ->first();

        if ($userPermission) {
            $hasCompanyAccess = $this->checkCompanyAccess($user, $userPermission->companycode);
            
            if ($hasCompanyAccess) {
                $result = $userPermission->permissiontype === 'GRANT';
                
                Log::info('User-specific permission override', [
                    'userid' => $user->userid,
                    'permission' => $permissionName,
                    'type' => $userPermission->permissiontype,
                    'company' => $userPermission->companycode,
                    'result' => $result
                ]);
                
                return $result;
            }
        }

        // Step 3: Check role-based (jabatan) permissions
        if ($user->idjabatan) {
            $jabatanPermission = JabatanPermission::where('idjabatan', $user->idjabatan)
                                                 ->where('permissionid', $permissionModel->permissionid)
                                                 ->where('isactive', 1)
                                                 ->first();

            if ($jabatanPermission) {
                Log::info('Jabatan permission granted', [
                    'userid' => $user->userid,
                    'jabatan' => $user->idjabatan,
                    'permission' => $permissionName
                ]);
                
                return true;
            }
        }

        // Step 4: No permission found
        Log::info('Permission denied - not found in new system', [
            'userid' => $user->userid,
            'permission' => $permissionName,
            'jabatan' => $user->idjabatan
        ]);
        
        return false;
    }

    /**
     * Check if user has access to specific company
     */
    private function checkCompanyAccess(User $user, string $companycode): bool
    {
        $userCompany = UserCompany::where('userid', $user->userid)
                                  ->where('companycode', $companycode)
                                  ->where('isactive', 1)
                                  ->first();
        
        return $userCompany !== null;
    }

    /**
     * Get all effective permissions for a user (for debugging/admin purposes)
     */
    public static function getUserEffectivePermissions(User $user): array
    {
        $effectivePermissions = [];
        
        // Get jabatan permissions
        if ($user->idjabatan) {
            $jabatanPermissions = JabatanPermission::join('permissions', 'jabatanpermissions.permissionid', '=', 'permissions.permissionid')
                                                  ->where('jabatanpermissions.idjabatan', $user->idjabatan)
                                                  ->where('jabatanpermissions.isactive', 1)
                                                  ->where('permissions.isactive', 1)
                                                  ->select('permissions.permissionname', 'permissions.category')
                                                  ->get();

            foreach ($jabatanPermissions as $perm) {
                $effectivePermissions[$perm->permissionname] = [
                    'source' => 'jabatan',
                    'category' => $perm->category,
                    'granted' => true
                ];
            }
        }

        // Override with user-specific permissions
        $userPermissions = UserPermission::join('permissions', 'userpermission.permissionid', '=', 'permissions.permissionid')
                                        ->where('userpermission.userid', $user->userid)
                                        ->where('userpermission.isactive', 1)
                                        ->where('permissions.isactive', 1)
                                        ->select('permissions.permissionname', 'permissions.category', 'userpermission.permissiontype', 'userpermission.companycode')
                                        ->get();

        foreach ($userPermissions as $perm) {
            // Check if user has access to the company
            $hasCompanyAccess = UserCompany::where('userid', $user->userid)
                                          ->where('companycode', $perm->companycode)
                                          ->where('isactive', 1)
                                          ->exists();

            if ($hasCompanyAccess) {
                $effectivePermissions[$perm->permissionname] = [
                    'source' => 'user_override',
                    'category' => $perm->category,
                    'granted' => $perm->permissiontype === 'GRANT',
                    'company' => $perm->companycode
                ];
            }
        }

        return $effectivePermissions;
    }
}