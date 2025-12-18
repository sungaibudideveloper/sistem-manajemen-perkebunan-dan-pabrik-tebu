<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Report;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Report\DthReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DthReportController extends Controller
{
    protected $dthService;

    public function __construct(DthReportService $dthService)
    {
        $this->dthService = $dthService;
    }

    public function generate(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $url = route('transaction.rencanakerjaharian.dth-report', ['date' => $request->date]);
        
        return response()->json([
            'success' => true,
            'message' => 'Membuka laporan DTH...',
            'redirect_url' => $url
        ]);
    }

    public function show(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        return view('transaction.rencanakerjaharian.report.report-dth', ['date' => $date]);
    }

    public function getData(Request $request)
    {
        try {
            $date = $request->query('date', date('Y-m-d'));
            $companycode = Session::get('companycode');
            
            $payload = $this->dthService->buildDthPayload($companycode, $date);
            
            return response()->json(array_merge(['success' => true], $payload));
            
        } catch (\Exception $e) {
            \Log::error("DTH Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data DTH: ' . $e->getMessage()
            ], 500);
        }
    }
}