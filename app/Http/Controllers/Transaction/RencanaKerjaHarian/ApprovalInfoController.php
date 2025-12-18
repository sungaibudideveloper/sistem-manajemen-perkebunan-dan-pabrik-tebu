<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Rkh\RkhService;
use App\Services\Transaction\RencanaKerjaHarian\Lkh\LkhService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * ApprovalInfoController
 * 
 * Handles RKH & LKH approval info (READ-ONLY)
 * No approval actions here, just display status
 */
class ApprovalInfoController extends Controller
{
    protected $rkhService;
    protected $lkhService;

    public function __construct(RkhService $rkhService, LkhService $lkhService)
    {
        $this->rkhService = $rkhService;
        $this->lkhService = $lkhService;
    }

    // ============ RKH APPROVAL INFO ============
    
    public function getRkhApprovalDetail($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $data = $this->rkhService->getApprovalDetail($rkhno, $companycode);

            if (!$data) {
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan']);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            \Log::error("Error getting RKH approval detail: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail approval'], 500);
        }
    }

    public function updateRkhStatus(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'status' => 'required|string'
        ]);

        try {
            $companycode = Session::get('companycode');
            $result = $this->rkhService->updateRkhStatus(
                $request->rkhno,
                $request->status,
                Auth::user(),
                $companycode
            );

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            \Log::error("Error updating RKH status: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal update status'], 500);
        }
    }

    // ============ LKH APPROVAL INFO ============
    
    public function getLkhApprovalDetail($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $data = $this->lkhService->getLkhApprovalDetail($lkhno, $companycode);

            if (!$data) {
                return response()->json(['success' => false, 'message' => 'LKH tidak ditemukan']);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            \Log::error("Error getting LKH approval detail: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail approval'], 500);
        }
    }
}