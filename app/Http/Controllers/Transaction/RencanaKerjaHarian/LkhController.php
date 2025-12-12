<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\LkhService;
use App\Services\Transaction\RencanaKerjaHarian\LkhGeneratorService;
use App\Services\Transaction\RencanaKerjaHarian\MaterialUsageGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

/**
 * LkhController
 * 
 * Slim controller for LKH (Laporan Kegiatan Harian) operations
 * Delegates all business logic to LkhService
 */
class LkhController extends Controller
{
    protected LkhService $lkhService;
    protected LkhGeneratorService $lkhGenerator;
    protected MaterialUsageGeneratorService $materialGenerator;

    public function __construct(
        LkhService $lkhService,
        LkhGeneratorService $lkhGenerator,
        MaterialUsageGeneratorService $materialGenerator
    ) {
        $this->lkhService = $lkhService;
        $this->lkhGenerator = $lkhGenerator;
        $this->materialGenerator = $materialGenerator;
    }

    /**
     * Get LKH data for specific RKH
     */
    public function getLKHData(string $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $result = $this->lkhService->getLkhListForRkh($companycode, $rkhno);

            return response()->json([
                'success' => true,
                'lkh_data' => $result['lkh_data'],
                'rkhno' => $result['rkhno'],
                'can_generate_lkh' => $result['can_generate_lkh'],
                'generate_message' => $result['generate_message'],
                'total_lkh' => $result['total_lkh']
            ]);

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
     * Show LKH report (Normal/Panen/BSM)
     */
    public function showLKH(string $lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $lkhData = $this->lkhService->getLkhDetail($companycode, $lkhno);

            // Route to different views based on activity type
            $viewMap = [
                'bsm' => 'transaction.rencanakerjaharian.lkh-report-bsm',
                'panen' => 'transaction.rencanakerjaharian.lkh-report-panen',
                'normal' => 'transaction.rencanakerjaharian.lkh-report'
            ];

            $view = $viewMap[$lkhData['activityType']] ?? $viewMap['normal'];

            return view($view, array_merge([
                'title' => $this->getLkhTitle($lkhData['activityType']),
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
            ], $lkhData));

        } catch (\Exception $e) {
            \Log::error("Error showing LKH: " . $e->getMessage());
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan LKH: ' . $e->getMessage());
        }
    }

    /**
     * Show LKH edit form
     */
    public function editLKH(string $lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $lkhData = $this->lkhService->getLkhDetail($companycode, $lkhno);

            // Security check
            if ($lkhData['lkhData']->issubmit) {
                return redirect()->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'LKH sudah disubmit dan tidak dapat diedit');
            }

            $formData = $this->loadLkhEditFormData($companycode);

            return view('transaction.rencanakerjaharian.edit-lkh', array_merge([
                'title' => 'Edit LKH',
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
            ], $lkhData, $formData));

        } catch (\Exception $e) {
            \Log::error("Error editing LKH: " . $e->getMessage());
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', 'Terjadi kesalahan saat membuka edit LKH: ' . $e->getMessage());
        }
    }

    /**
     * Update LKH
     */
    public function updateLKH(Request $request, string $lkhno)
    {
        try {
            $this->validateLkhUpdateRequest($request);

            $companycode = Session::get('companycode');
            
            $result = $this->lkhService->updateLkh($companycode, $lkhno, $request->all());

            if ($result['success']) {
                return redirect()->route('transaction.rencanakerjaharian.showLKH', $lkhno)
                    ->with('success', $result['message']);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            \Log::error("Error updating LKH: " . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengupdate LKH: ' . $e->getMessage());
        }
    }

    /**
     * Submit LKH for approval
     */
    public function submitLKH(Request $request)
    {
        $request->validate(['lkhno' => 'required|string']);

        try {
            $companycode = Session::get('companycode');
            
            $result = $this->lkhService->submitLkh($companycode, $request->lkhno);

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            \Log::error("Error submitting LKH: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending LKH approvals
     */
    public function getPendingLKHApprovals(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            
            if (!$currentUser->idjabatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki jabatan yang valid'
                ]);
            }

            $pendingLkh = $this->lkhService->getPendingApprovalsForUser($companycode, $currentUser);

            return response()->json([
                'success' => true,
                'data' => $pendingLkh->toArray(),
                'user_info' => [
                    'userid' => $currentUser->userid,
                    'name' => $currentUser->name,
                    'idjabatan' => $currentUser->idjabatan,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting pending LKH approvals: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data approval LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process LKH approval
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

            $result = $this->lkhService->processApproval(
                $companycode,
                $request->lkhno,
                $request->level,
                $request->action,
                $currentUser
            );

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            \Log::error("Error processing LKH approval: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses approval LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get LKH approval detail
     */
    public function getLkhApprovalDetail(string $lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $detail = $this->lkhService->getLkhDetail($companycode, $lkhno);

            return response()->json([
                'success' => true,
                'data' => $this->formatLkhApprovalDetail($detail['lkhData'])
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting LKH approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual generate LKH from RKH
     */
    public function manualGenerateLkh(Request $request, string $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $result = $this->lkhGenerator->generateLkhFromRkh($rkhno, $companycode);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Manual generate LKH error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get material usage data
     */
    public function getMaterialUsageApi(string $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $result = $this->materialGenerator->getMaterialUsageData($companycode, $rkhno);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'rkhno' => $rkhno,
                    'material_data' => $result['data']
                ]);
            }

            return response()->json($result, 500);

        } catch (\Exception $e) {
            \Log::error("Get material usage error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data material: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate material usage
     */
    public function generateMaterialUsage(Request $request)
    {
        $request->validate(['rkhno' => 'required|string']);
        
        try {
            $result = $this->materialGenerator->generateMaterialUsageFromRkh($request->rkhno);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Error manual generate material usage: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate material usage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get plot info for activity
     */
    public function getPlotInfo(string $plot, string $activitycode)
    {
        try {
            $companycode = Session::get('companycode');
            
            $result = $this->lkhService->getPlotInfo($companycode, $plot, $activitycode);

            return response()->json([
                'success' => true,
                'plot' => $result['plot'],
                'activitycode' => $result['activitycode'],
                'luasplot' => $result['luasplot'],
                'totalsudahdikerjakan' => $result['totalsudahdikerjakan'],
                'luassisa' => $result['luassisa'],
                'tanggal' => $result['tanggal'],
                'ispanen' => $result['ispanen'],
                'batchinfo' => $result['batchinfo'] ?? null,
                'blok' => $result['blok'] ?? null,
                'activebatchno' => $result['activebatchno'] ?? null,
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting plot info for {$plot}/{$activitycode}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat info plot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get surat jalan list for plot
     */
    public function getSuratJalan(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $plot = $request->query('plot');
            $subkontraktorId = $request->query('subkontraktor_id');
            $lkhno = $request->query('lkhno');
            
            if (!$plot || !$subkontraktorId || !$lkhno) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap'
                ], 400);
            }
            
            $suratJalan = $this->lkhService->getSuratJalanForPlot($companycode, $plot, $subkontraktorId, $lkhno);
            
            return response()->json([
                'success' => true,
                'surat_jalan' => $suratJalan->toArray(),
                'subkontraktor_nama' => $suratJalan->first()->namasubkontraktor ?? null,
                'total' => $suratJalan->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error getting surat jalan list: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data surat jalan: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Get LKH title based on activity type
     */
    private function getLkhTitle(string $activityType): string
    {
        return match($activityType) {
            'bsm' => 'Laporan Kegiatan Harian (LKH) - Cek BSM',
            'panen' => 'Laporan Kegiatan Harian (LKH) - Panen',
            default => 'Laporan Kegiatan Harian (LKH)'
        };
    }

    /**
     * Validate LKH update request
     */
    private function validateLkhUpdateRequest(Request $request): void
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:500',
            'plots' => 'nullable|array',
            'plots.*.blok' => 'required_with:plots|string',
            'plots.*.plot' => 'required_with:plots|string',
            'plots.*.luasrkh' => 'required_with:plots|numeric|min:0',
            'plots.*.luashasil' => 'required_with:plots|numeric|min:0',
            'plots.*.luassisa' => 'required_with:plots|numeric|min:0',
            'workers' => 'nullable|array',
            'workers.*.tenagakerjaid' => 'required_with:workers|string',
            'workers.*.jammasuk' => 'nullable|date_format:H:i',
            'workers.*.jamselesai' => 'nullable|date_format:H:i',
            'workers.*.totaljamkerja' => 'nullable|numeric|min:0',
            'workers.*.overtimehours' => 'nullable|numeric|min:0',
            'workers.*.premi' => 'nullable|numeric|min:0',
            'workers.*.upahharian' => 'nullable|numeric|min:0',
            'workers.*.upahborongan' => 'nullable|numeric|min:0',
            'workers.*.totalupah' => 'nullable|numeric|min:0',
            'materials' => 'nullable|array',
            'materials.*.itemcode' => 'required_with:materials|string',
            'materials.*.qtyditerima' => 'required_with:materials|numeric|min:0',
            'materials.*.qtysisa' => 'required_with:materials|numeric|min:0',
        ]);
    }

    /**
     * Load LKH edit form data
     */
    private function loadLkhEditFormData(string $companycode): array
    {
        return [
            'tenagaKerja' => \DB::table('tenagakerja')
                ->where('companycode', $companycode)
                ->where('isactive', 1)
                ->select(['tenagakerjaid', 'nama', 'nik', 'jenistenagakerja'])
                ->orderBy('nama')
                ->get(),
            'bloks' => \DB::table('blok')
                ->where('companycode', $companycode)
                ->orderBy('blok')
                ->get(),
            'masterlist' => \DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('isactive', 1)
                ->orderBy('plot')
                ->get(),
            'plots' => \DB::table('plot')
                ->where('companycode', $companycode)
                ->get(),
        ];
    }

    /**
     * Format LKH approval detail
     */
    private function formatLkhApprovalDetail(object $lkh): array
    {
        $levels = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $jabatanId = $lkh->{"approval{$i}idjabatan"} ?? null;
            if (!$jabatanId) continue;

            $flag = $lkh->{"approval{$i}flag"} ?? null;
            $status = 'waiting';
            $statusText = 'Waiting';

            if ($flag === '1') {
                $status = 'approved';
                $statusText = 'Approved';
            } elseif ($flag === '0') {
                $status = 'declined';
                $statusText = 'Declined';
            }

            $levels[] = [
                'level' => $i,
                'status' => $status,
                'status_text' => $statusText,
                'date_formatted' => $lkh->{"approval{$i}date"} 
                    ? Carbon::parse($lkh->{"approval{$i}date"})->format('d/m/Y H:i') 
                    : null
            ];
        }

        return [
            'lkhno' => $lkh->lkhno,
            'rkhno' => $lkh->rkhno,
            'lkhdate' => $lkh->lkhdate,
            'lkhdate_formatted' => Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
            'mandor_nama' => $lkh->mandornama ?? 'Unknown',
            'activityname' => $lkh->activityname ?? 'Unknown Activity',
            'jumlah_approval' => $lkh->jumlahapproval ?? 0,
            'levels' => $levels
        ];
    }
}