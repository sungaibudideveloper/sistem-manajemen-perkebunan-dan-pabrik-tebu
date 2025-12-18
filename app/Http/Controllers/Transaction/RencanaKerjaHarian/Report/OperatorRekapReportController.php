<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Report;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Report\OperatorRekapReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OperatorRekapReportController extends Controller
{
    protected $operatorRekapService;

    public function __construct(OperatorRekapReportService $operatorRekapService)
    {
        $this->operatorRekapService = $operatorRekapService;
    }

    /**
     * Generate report URL
     */
    public function generate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);
        
        $url = route('transaction.rencanakerjaharian.operator-rekap-report', [
            'date' => $request->date
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Membuka rekap laporan operator...',
            'redirect_url' => $url
        ]);
    }

    /**
     * Show report page
     */
    public function show(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        
        return view('transaction.rencanakerjaharian.report.report-operator-rekap', [
            'title' => 'Rekap Laporan Operator',
            'navbar' => 'Transaction',
            'nav' => 'Rencana Kerja Harian - Rekap Laporan Operator',
            'date' => $date
        ]);
    }

    /**
     * Get report data (AJAX)
     */
    public function getData(Request $request)
    {
        try {
            $date = $request->query('date', date('Y-m-d'));
            $companycode = Session::get('companycode');
            
            // Sekarang return data.all_activities juga
            $result = $this->operatorRekapService->buildOperatorRekapReportPayload($companycode, $date);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Error getting operator rekap report data: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
}