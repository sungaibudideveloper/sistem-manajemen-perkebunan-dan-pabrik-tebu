<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Report;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * DthController
 * 
 * Handles DTH (Distribusi Tenaga Harian) Report
 * Shows daily worker distribution: Harian, Borongan, and Alat (vehicles)
 */
class DthController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Show DTH report view
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        
        return view('transaction.rencanakerjaharian.dth-report', [
            'title' => 'Laporan DTH (Distribusi Tenaga Harian)',
            'navbar' => 'Report',
            'nav' => 'DTH Report',
            'date' => $date
        ]);
    }

    /**
     * Get DTH report data (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $companycode = Session::get('companycode');
        $date = $request->query('date', date('Y-m-d'));

        $result = $this->reportService->getDthReport($companycode, $date);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    /**
     * Generate DTH report (redirect to report page)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);
        
        $url = route('transaction.rencanakerjaharian.report.dth.index', [
            'date' => $request->date
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Membuka laporan DTH...',
            'redirect_url' => $url
        ]);
    }
}