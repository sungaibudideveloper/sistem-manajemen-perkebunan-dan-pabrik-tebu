<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserManagement\PermissionService;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Display permission listing
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'modules' => $request->get('modules') ? explode(',', $request->get('modules')) : []
        ];

        $perPage = $request->get('perPage', 20);
        $result = $this->permissionService->getPaginatedPermissions($filters, $perPage);
        $modules = $this->permissionService->getModules();

        return view('usermanagement.permission.index', [
            'title' => 'Permission Master Data',
            'navbar' => 'User Management',
            'nav' => 'Permissions',
            'result' => $result,
            'modules' => $modules, // ✅ Ini yang dipake
            'perPage' => $perPage
        ]);
    }

    /**
     * Store new permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:30',
            'resource' => 'required|string|max:50',
            'action' => 'required|string|max:30',
            'displayname' => 'required|string|max:100',
            'description' => 'nullable|string',
            'isactive' => 'boolean'
        ]);

        $result = $this->permissionService->createPermission($validated);

        return redirect()->route('usermanagement.permission.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Update permission
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:30',
            'resource' => 'required|string|max:50',
            'action' => 'required|string|max:30',
            'displayname' => 'required|string|max:100',
            'description' => 'nullable|string',
            'isactive' => 'boolean'
        ]);

        $result = $this->permissionService->updatePermission($id, $validated);

        return redirect()->route('usermanagement.permission.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Soft delete permission
     */
    public function destroy($id)
    {
        $result = $this->permissionService->deletePermission($id);

        return redirect()->route('usermanagement.permission.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}