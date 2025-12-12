<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Report;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * OperatorController
 * 
 * Handles Operator Daily Report
 * Shows operator activities, vehicle usage, fuel consumption
 */
class OperatorController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get list of operators who worked on specific date (AJAX)
     * Used for modal/dropdown selection
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOperatorsForDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $companycode = Session::get('companycode');
        $date = $request->query('date', date('Y-m-d'));

        $result = $this->reportService->getOperatorsForDate($companycode, $date);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    /**
     * Show operator report view
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $operatorId = $request->query('operator_id');
        
        if (!$operatorId) {
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', 'Operator ID tidak ditemukan');
        }
        
        return view('transaction.rencanakerjaharian.lkh-report-operator', [
            'title' => 'Laporan Operator',
            'navbar' => 'Report',
            'nav' => 'Operator Report',
            'date' => $date,
            'operator_id' => $operatorId
        ]);
    }

    /**
     * Get operator daily report data (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'operator_id' => 'required|string'
        ]);

        $companycode = Session::get('companycode');
        $operatorId = $request->query('operator_id');
        $date = $request->query('date', date('Y-m-d'));

        $result = $this->reportService->getOperatorReport($companycode, $operatorId, $date);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    /**
     * Generate operator report (redirect to report page)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'operator_id' => 'required|string'
        ]);
        
        $url = route('transaction.rencanakerjaharian.report.operator.index', [
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
     * Get operator monthly performance (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlyPerformance(Request $request)
    {
        $request->validate([
            'operator_id' => 'required|string',
            'month_year' => 'required|date_format:Y-m'
        ]);

        $companycode = Session::get('companycode');
        $operatorId = $request->query('operator_id');
        $monthYear = $request->query('month_year');

        $result = $this->reportService->getOperatorMonthlyPerformance($companycode, $operatorId, $monthYear);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    /**
     * Get operators comparison for date (AJAX)
     * Compare all operators performance
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComparison(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $companycode = Session::get('companycode');
        $date = $request->query('date', date('Y-m-d'));

        $result = $this->reportService->getOperatorsComparison($companycode, $date);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}