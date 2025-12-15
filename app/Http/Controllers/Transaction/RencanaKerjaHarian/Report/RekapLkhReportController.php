<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Report;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Report\RekapLkhReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RekapLkhReportController extends Controller
{
    protected $rekapService;

    public function __construct(RekapLkhReportService $rekapService)
    {
        $this->rekapService = $rekapService;
    }

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

    public function show(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        return view('transaction.rencanakerjaharian.lkh-rekap', ['date' => $date]);
    }

    public function getData(Request $request)
    {
        try {
            $date = $request->query('date', date('Y-m-d'));
            $companycode = Session::get('companycode');
            
            $payload = $this->rekapService->buildRekapPayload($companycode, $date);
            
            return response()->json(array_merge(['success' => true], $payload));
            
        } catch (\Exception $e) {
            \Log::error("LKH Rekap Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data LKH Rekap: ' . $e->getMessage()
            ], 500);
        }
    }
}