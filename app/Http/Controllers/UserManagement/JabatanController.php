<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserManagement\{JabatanService, PermissionService};

class JabatanController extends Controller
{
    protected JabatanService $jabatanService;
    protected PermissionService $permissionService;

    public function __construct(JabatanService $jabatanService, PermissionService $permissionService)
    {
        $this->jabatanService = $jabatanService;
        $this->permissionService = $permissionService;
    }

    public function index(Request $request)
    {
        $filters = ['search' => $request->get('search')];
        $perPage = $request->get('perPage', 10);

        $result = $this->jabatanService->getPaginatedJabatan($filters, $perPage);
        $permissions = $this->permissionService->getAllGroupedByModule();

        return view('usermanagement.jabatan.index', [
            'title' => 'Jabatan Management',
            'navbar' => 'User Management',
            'nav' => 'Jabatan',
            'result' => $result,
            'permissions' => $permissions,
            'perPage' => $perPage
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'namajabatan' => 'required|string|max:30'
        ]);

        $result = $this->jabatanService->createJabatan($validated, auth()->user()->userid);

        return redirect()->route('usermanagement.jabatan.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function update(Request $request, $idjabatan)
    {
        $validated = $request->validate([
            'namajabatan' => 'required|string|max:30'
        ]);

        $result = $this->jabatanService->updateJabatan($idjabatan, $validated, auth()->user()->userid);

        return redirect()->route('usermanagement.jabatan.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function destroy($idjabatan)
    {
        $result = $this->jabatanService->deleteJabatan($idjabatan);

        return redirect()->route('usermanagement.jabatan.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function assignPermissions(Request $request)
    {
        $validated = $request->validate([
            'idjabatan' => 'required|integer|exists:jabatan,idjabatan',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permission,id'
        ]);

        $permissionIds = $validated['permissions'] ?? [];
        $result = $this->jabatanService->assignPermissions(
            $validated['idjabatan'],
            $permissionIds,
            auth()->user()->userid
        );

        return redirect()->route('usermanagement.jabatan.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function getPermissions($idjabatan)
    {
        $result = $this->jabatanService->getJabatanPermissions($idjabatan);
        return response()->json($result);
    }
}