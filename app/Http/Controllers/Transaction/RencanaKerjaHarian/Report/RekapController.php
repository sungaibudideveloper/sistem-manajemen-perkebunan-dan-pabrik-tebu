<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Report;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * RekapController
 * 
 * Handles LKH Rekap Report
 * Groups LKH activities by activity group (Pengolahan, Perawatan, Panen, Pias, Lain-lain)
 */
class RekapController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Show LKH Rekap report view
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        
        return view('transaction.rencanakerjaharian.lkh-rekap', [
            'title' => 'Laporan Rekap LKH',
            'navbar' => 'Report',
            'nav' => 'Rekap LKH',
            'date' => $date
        ]);
    }

    /**
     * Get LKH Rekap data (AJAX)
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

        $result = $this->reportService->getRekapLkhReport($companycode, $date);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    /**
     * Generate LKH Rekap report (redirect to report page)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);
        
        $url = route('transaction.rencanakerjaharian.report.rekap.index', [
            'date' => $request->date
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Membuka laporan Rekap LKH...',
            'redirect_url' => $url
        ]);
    }
}