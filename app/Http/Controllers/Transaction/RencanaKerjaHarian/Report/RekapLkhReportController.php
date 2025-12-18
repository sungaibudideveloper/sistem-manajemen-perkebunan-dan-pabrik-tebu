<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Report;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Report\RekapLkhReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class RekapLkhReportController extends Controller
{
    protected $rekapService;

    public function __construct(RekapLkhReportService $rekapService)
    {
        $this->rekapService = $rekapService;
    }

    /**
     * Generate report redirect
     */
    public function generate(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $url = route('transaction.rencanakerjaharian.rekap-lkh-report', ['date' => $request->date]);
        
        return response()->json([
            'success' => true,
            'message' => 'Membuka laporan Rekap LKH...',
            'redirect_url' => $url
        ]);
    }

    /**
     * Show report view
     */
    public function show(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        return view('transaction.rencanakerjaharian.report.report-rekap-lkh', ['date' => $date]);
    }

    /**
     * Get LKH Rekap data
     * ✅ UPDATED: Add company_info and summary statistics
     */
    public function getData(Request $request)
    {
        try {
            $date = $request->query('date', date('Y-m-d'));
            $companycode = Session::get('companycode');
            
            // Get company info
            $companyInfo = $this->getCompanyInfo($companycode);
            
            // Get payload from service (includes summary)
            $payload = $this->rekapService->buildRekapPayload($companycode, $date);
            
            // ✅ FIXED: Return with proper structure
            return response()->json([
                'success' => true,
                'company_info' => $companyInfo,
                'pengolahan' => $payload['pengolahan'],
                'perawatan' => $payload['perawatan'],
                'panen' => $payload['panen'],
                'pias' => $payload['pias'],
                'lainlain' => $payload['lainlain'],
                'summary' => $payload['summary'], // ✅ NEW: Summary stats
                'date' => $date,
                'generated_at' => now()->format('d/m/Y H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Log::error("LKH Rekap Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data LKH Rekap: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company info for header
     * 
     * @param string $companycode
     * @return string
     */
    private function getCompanyInfo($companycode)
    {
        $companyInfo = DB::table('company')
            ->where('companycode', $companycode)
            ->select('companycode', 'name')
            ->first();
        
        return $companyInfo 
            ? "{$companyInfo->companycode} - {$companyInfo->name}" 
            : $companycode;
    }
}