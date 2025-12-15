<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Approval;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Approval\LkhApprovalService;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * LkhApprovalController
 * 
 * Handles LKH approval HTTP requests.
 * RULE: Thin controller - only routing, validation, response formatting.
 */
class LkhApprovalController extends Controller
{
    protected $lkhApprovalService;
    protected $masterDataRepo;

    public function __construct(
        LkhApprovalService $lkhApprovalService,
        MasterDataRepository $masterDataRepo
    ) {
        $this->lkhApprovalService = $lkhApprovalService;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Get pending LKH approvals for current user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingLKHApprovals(Request $request)
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

            $formattedData = $this->lkhApprovalService->getPendingApprovals($currentUser, $companycode);

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'user_info' => $this->getUserInfo($currentUser)
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting pending LKH approvals: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get LKH approval detail
     * 
     * @param string $lkhno
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLkhApprovalDetail($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $formattedData = $this->lkhApprovalService->getApprovalDetail($lkhno, $companycode);

            if (!$formattedData) {
                return response()->json(['success' => false, 'message' => 'LKH tidak ditemukan']);
            }

            return response()->json(['success' => true, 'data' => $formattedData]);

        } catch (\Exception $e) {
            \Log::error("Error getting LKH approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process LKH approval (approve/decline)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processLKHApproval(Request $request)
    {
        $request->validate([
            'lkhno' => 'required|string',
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

            $result = $this->lkhApprovalService->processApproval(
                $request->lkhno,
                $request->action,
                $request->level,
                $currentUser,
                $companycode
            );
            
            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error processing LKH approval: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses approval: ' . $e->getMessage()
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