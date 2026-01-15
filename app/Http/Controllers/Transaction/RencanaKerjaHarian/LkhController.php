<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Lkh\LkhService;
use App\Services\Transaction\RencanaKerjaHarian\Lkh\LkhValidationService;
use App\Services\Transaction\RencanaKerjaHarian\Generator\LkhGeneratorService;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * LkhController
 * 
 * Handles LKH management HTTP requests.
 * RULE: Thin controller - only routing, validation, response formatting.
 */
class LkhController extends Controller
{
    protected $lkhService;
    protected $validationService;
    protected $masterDataRepo;

    public function __construct(
        LkhService $lkhService,
        LkhValidationService $validationService,
        MasterDataRepository $masterDataRepo
    ) {
        $this->lkhService = $lkhService;
        $this->validationService = $validationService;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Get LKH data for specific RKH
     * 
     * @param string $rkhno
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLKHData($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $result = $this->lkhService->getLkhListForRkh($rkhno, $companycode);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error getting LKH data for RKH {$rkhno}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data LKH: ' . $e->getMessage(),
                'lkh_data' => [],
                'total_lkh' => 0
            ], 500);
        }
    }

    /**
     * Show LKH report (detects activity type and routes to correct view)
     * 
     * @param string $lkhno
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showLKH($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $pageData = $this->lkhService->getShowLkhPageData($lkhno, $companycode);

            if (!$pageData) {
                return redirect()->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }

            // Add borongan rate for borongan workers - ONLY from database
            if ($pageData['lkhData']->jenistenagakerja == 2) {
                $pageData['boronganRate'] = $this->masterDataRepo->getBoronganRate(
                    $companycode, 
                    $pageData['lkhData']->activitycode, 
                    $pageData['lkhData']->lkhdate
                );
            }

            $activityType = $pageData['activity_type'];
            
            // Route based on activity type
            if ($activityType === 'bsm') {
                return view('transaction.rencanakerjaharian.lkh-report-bsm', array_merge([
                    'title' => 'Laporan Kegiatan Harian (LKH) - Cek BSM',
                    'navbar' => 'Input',
                    'nav' => 'Rencana Kerja Harian',
                ], $pageData));
            }
            
            if ($activityType === 'panen') {
                return view('transaction.rencanakerjaharian.lkh-report-panen', array_merge([
                    'title' => 'Laporan Kegiatan Harian (LKH) - Panen',
                    'navbar' => 'Input',
                    'nav' => 'Rencana Kerja Harian',
                ], $pageData));
            }
            
            // Default: Normal activity
            return view('transaction.rencanakerjaharian.lkh-report', array_merge([
                'title' => 'Laporan Kegiatan Harian (LKH)',
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
            ], $pageData));

        } catch (\Exception $e) {
            \Log::error("Error showing LKH: " . $e->getMessage());
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan LKH: ' . $e->getMessage());
        }
    }

    /**
     * Show LKH edit form
     * 
     * @param string $lkhno
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function editLKH($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $pageData = $this->lkhService->getEditLkhPageData($lkhno, $companycode);

            if (!$pageData) {
                return redirect()->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }

            return view('transaction.rencanakerjaharian.lkh-edit', array_merge([
                'title' => 'Edit LKH',
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
            ], $pageData));

        } catch (\Exception $e) {
            \Log::error("Error editing LKH: " . $e->getMessage());
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update LKH record
     * 
     * @param Request $request
     * @param string $lkhno
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLKH(Request $request, $lkhno)
    {
        try {
            // Validate
            $this->validationService->validateLkhUpdateRequest($request);

            // Prepare DTO
            $dto = [
                'keterangan' => $request->input('keterangan'),
                'plots' => $request->input('plots'),
                'workers' => $request->input('workers'),
                'materials' => $request->input('materials'),
            ];

            // Update LKH
            $companycode = Session::get('companycode');
            $this->lkhService->updateLkh($lkhno, $dto, $companycode);

            return redirect()->route('transaction.rencanakerjaharian.showLKH', $lkhno)
                ->with('success', 'LKH berhasil diupdate');

        } catch (\Exception $e) {
            \Log::error("Error updating LKH: " . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengupdate LKH: ' . $e->getMessage());
        }
    }

    /**
     * Submit LKH for approval
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitLKH(Request $request)
    {
        $request->validate(['lkhno' => 'required|string']);

        try {
            $companycode = Session::get('companycode');
            $lkhno = $request->lkhno;

            // Validate can submit
            $validation = $this->validationService->validateCanSubmit($lkhno, $companycode);
            
            if (!$validation['success']) {
                return response()->json($validation);
            }

            // Submit LKH
            $result = $this->lkhService->submitLkh($lkhno, $companycode);
            
            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error submitting LKH: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate LKH manually (calls existing generator service)
     * 
     * @param Request $request
     * @param string $rkhno
     * @return \Illuminate\Http\JsonResponse
     */
    public function manualGenerateLkh(Request $request, $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Use existing LkhGeneratorService (kept as-is for now)
            $lkhGenerator = new LkhGeneratorService();
            $result = $lkhGenerator->generateLkhFromRkh($rkhno, $companycode);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Manual generate LKH error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate LKH: ' . $e->getMessage()
            ], 500);
        }
    }
}