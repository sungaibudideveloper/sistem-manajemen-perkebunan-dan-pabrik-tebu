<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Approval;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Approval\RkhApprovalService;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * RkhApprovalController
 * 
 * Handles RKH approval HTTP requests.
 * RULE: Thin controller - only routing, validation, response formatting.
 */
class RkhApprovalController extends Controller
{
    protected $rkhApprovalService;
    protected $masterDataRepo;

    public function __construct(
        RkhApprovalService $rkhApprovalService,
        MasterDataRepository $masterDataRepo
    ) {
        $this->rkhApprovalService = $rkhApprovalService;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Get pending RKH approvals for current user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingApprovals(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            
            if (!$this->validateUserForApproval($currentUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki jabatan yang valid'
                ]);
            }

            $formattedData = $this->rkhApprovalService->getPendingApprovals($currentUser, $companycode);

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'user_info' => $this->getUserInfo($currentUser)
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting pending approvals: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get RKH approval detail
     * 
     * @param string $rkhno
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApprovalDetail($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $formattedData = $this->rkhApprovalService->getApprovalDetail($rkhno, $companycode);

            if (!$formattedData) {
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan']);
            }

            return response()->json(['success' => true, 'data' => $formattedData]);

        } catch (\Exception $e) {
            \Log::error("Error getting approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process RKH approval (approve/decline)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processApproval(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            
            if (!$this->validateUserForApproval($currentUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki jabatan yang valid'
                ]);
            }

            $result = $this->rkhApprovalService->processApproval(
                $request->rkhno,
                $request->action,
                $request->level,
                $currentUser,
                $companycode
            );
            
            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error processing approval: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update RKH status (Completed/In Progress)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'status' => 'required|string'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            
            $result = $this->rkhApprovalService->updateRkhStatus(
                $request->rkhno,
                $request->status,
                $currentUser,
                $companycode
            );

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            \Log::error("Error updating RKH status: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    /**
     * Validate user for approval process
     */
    private function validateUserForApproval($currentUser)
    {
        return $currentUser && $currentUser->idjabatan;
    }

    /**
     * Get user info for response
     */
    private function getUserInfo($currentUser)
    {
        return [
            'userid' => $currentUser->userid,
            'name' => $currentUser->name,
            'idjabatan' => $currentUser->idjabatan,
            'jabatan_name' => $this->masterDataRepo->getJabatanName($currentUser->idjabatan)
        ];
    }
}