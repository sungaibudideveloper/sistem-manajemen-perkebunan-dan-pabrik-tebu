<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Report;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Report\OperatorReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OperatorReportController extends Controller
{
    protected $operatorService;

    public function __construct(OperatorReportService $operatorService)
    {
        $this->operatorService = $operatorService;
    }

    /**
     * Get list of operators for specific date (AJAX)
     */
    public function getOperatorsForDate(Request $request)
    {
        try {
            $date = $request->query('date', date('Y-m-d'));
            $companycode = Session::get('companycode');
            
            $operators = $this->operatorService->getOperatorsForDate($companycode, $date);
            
            return response()->json([
                'success' => true,
                'operators' => $operators
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error getting operators: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data operator: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate report URL
     */
    public function generate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'operator_id' => 'required|string'
        ]);
        
        $url = route('transaction.rencanakerjaharian.operator-report', [
            'date' => $request->date,
            'operator_id' => $request->operator_id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Membuka laporan operator...',
            'redirect_url' => $url
        ]);
    }

    /**
     * Show report page
     */
    public function show(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $operatorId = $request->query('operator_id');
        
        if (!$operatorId) {
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', 'Operator ID tidak ditemukan');
        }
        
        return view('transaction.rencanakerjaharian.report.report-operator', [
            'date' => $date,
            'operator_id' => $operatorId
        ]);
    }

    /**
     * Get report data (AJAX)
     */
    public function getData(Request $request)
    {
        try {
            $date = $request->query('date', date('Y-m-d'));
            $operatorId = $request->query('operator_id');
            $companycode = Session::get('companycode');
            
            if (!$operatorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operator ID tidak ditemukan'
                ], 400);
            }
            
            $result = $this->operatorService->buildOperatorReportPayload($companycode, $date, $operatorId);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Error getting operator report data: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data laporan operator: ' . $e->getMessage()
            ], 500);
        }
    }
}