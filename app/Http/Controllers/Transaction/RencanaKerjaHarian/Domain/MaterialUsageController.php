<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Domain;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Domain\MaterialUsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * MaterialUsageController
 * 
 * Handles material usage HTTP requests.
 * RULE: Thin controller - only routing, validation, response.
 */
class MaterialUsageController extends Controller
{
    protected $materialService;

    public function __construct(MaterialUsageService $materialService)
    {
        $this->materialService = $materialService;
    }

    /**
     * Get material usage data API
     * 
     * @param string $rkhno
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMaterialUsageApi($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $result = $this->materialService->buildMaterialUsagePayload($companycode, $rkhno);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Error getting material usage for RKH {$rkhno}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data material usage: ' . $e->getMessage(),
                'material_groups' => [],
                'total_items' => 0
            ], 500);
        }
    }

    /**
     * Generate material usage manually
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateMaterialUsage(Request $request)
    {
        $request->validate(['rkhno' => 'required|string']);
        
        try {
            $companycode = Session::get('companycode');
            
            $result = $this->materialService->generateMaterialUsage($companycode, $request->rkhno);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Error manual generate material usage: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate material usage: ' . $e->getMessage()
            ], 500);
        }
    }
}