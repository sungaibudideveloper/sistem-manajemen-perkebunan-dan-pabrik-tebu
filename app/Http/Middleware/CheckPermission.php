<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (!Auth::check()) {
            abort(401, 'Unauthenticated');
        }
        
        $user = Auth::user();
        $permissions = json_decode($user->permissions ?? '[]', true);
        
        /*
        // Debug logging
        Log::info('Permission Check Debug', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'required_permission' => $permission,
            'required_permission_length' => strlen($permission),
            'has_permission' => in_array($permission, $permissions),
            'total_permissions' => count($permissions)
        ]);
        */
        
        // Log dashboard permissions untuk debug
        $dashboardPerms = array_filter($permissions, function($p) {
            return stripos($p, 'dashboard') !== false;
        });
        
        Log::info('Dashboard Permissions', [
            'dashboard_permissions' => array_values($dashboardPerms),
            'searching_for' => $permission,
            'exact_match_found' => in_array($permission, $permissions)
        ]);
        
        // Additional debug - check for similar permissions
        foreach ($permissions as $userPerm) {
            if (stripos($userPerm, 'dashboard') !== false && 
                (stripos($userPerm, 'agronomi') !== false || stripos($userPerm, 'hpt') !== false)) {
                Log::info('Similar permission found', [
                    'user_permission' => $userPerm,
                    'user_permission_bytes' => bin2hex($userPerm),
                    'required_permission_bytes' => bin2hex($permission),
                    'exact_match' => $userPerm === $permission
                ]);
            }
        }
        
        if (!in_array($permission, $permissions)) {
            Log::warning('Permission denied', [
                'user' => $user->name,
                'required' => $permission,
                'available_permissions' => $permissions
            ]);
            abort(403, 'Unauthorized action. Required permission: ' . $permission);
        }
        
        return $next($request);
    }
}