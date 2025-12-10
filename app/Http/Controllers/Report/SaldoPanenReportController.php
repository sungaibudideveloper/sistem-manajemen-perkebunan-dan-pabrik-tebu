<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SaldoPanenReportController extends Controller
{
    /**
     * Display Saldo Panen Report
     */
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $selectedDate = $request->input('date', date('Y-m-d'));
        
        // Get mandor list for filter
        $mandors = DB::table('user')
            ->where('companycode', $companycode)
            ->where('idjabatan', 3) // Mandor
            ->where('isactive', 1)
            ->orderBy('name')
            ->get();
        
        return view('report.saldo-panen.index', [
            'title' => 'Report Saldo Panen',
            'navbar' => 'Report',
            'nav' => 'Saldo Panen',
            'selectedDate' => $selectedDate,
            'mandors' => $mandors
        ]);
    }
    
    /**
     * Get Saldo Panen Data (API endpoint)
     */
    public function getData(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $date = $request->input('date', date('Y-m-d'));
            $mandorId = $request->input('mandor_id');
            $lifecycle = $request->input('lifecycle');
            $progressFilter = $request->input('progress_filter');
            $search = $request->input('search');
            
            // Build query
            $query = $this->buildSaldoPanenQuery($companycode, $date);
            
            // Apply filters
            if ($mandorId) {
                $query->where('last_mandor_id', $mandorId);
            }
            
            if ($lifecycle) {
                $query->where('b.lifecyclestatus', $lifecycle);
            }
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('m.plot', 'like', "%{$search}%")
                      ->orWhere('b.batchno', 'like', "%{$search}%");
                });
            }
            
            $saldoData = $query->get();
            
            // Apply progress filter (post-query because it's calculated)
            if ($progressFilter) {
                $saldoData = $saldoData->filter(function($item) use ($progressFilter) {
                    $progress = $item->progress;
                    switch ($progressFilter) {
                        case 'low':
                            return $progress < 50;
                        case 'medium':
                            return $progress >= 50 && $progress < 90;
                        case 'high':
                            return $progress >= 90;
                        default:
                            return true;
                    }
                });
            }
            
            // Calculate summary
            $summary = $this->calculateSummary($saldoData);
            
            // Format data
            $formattedData = $this->formatSaldoData($saldoData);
            
            return response()->json([
                'success' => true,
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y'),
                'data' => $formattedData,
                'summary' => $summary,
                'company_info' => $this->getCompanyInfo($companycode)
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error getting Saldo Panen data: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Build base query for Saldo Panen
     */
    private function buildSaldoPanenQuery($companycode, $date)
    {
        return DB::table('masterlist as m')
            ->join('batch as b', function($join) use ($companycode) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                    ->where('b.companycode', '=', $companycode);
            })
            ->leftJoin(DB::raw('(
                SELECT 
                    ldp.plot,
                    ldp.batchno,
                    SUM(ldp.luashasil) as total_dipanen,
                    MAX(lh.lkhdate) as last_harvest_date,
                    MAX(lh.mandorid) as last_mandor_id
                FROM lkhdetailplot ldp
                JOIN lkhhdr lh ON ldp.lkhno = lh.lkhno AND ldp.companycode = lh.companycode
                WHERE ldp.companycode = "' . $companycode . '"
                    AND lh.approvalstatus = "1"
                    AND lh.lkhdate <= "' . $date . '"
                GROUP BY ldp.plot, ldp.batchno
            ) as harvest_summary'), function($join) {
                $join->on('m.plot', '=', 'harvest_summary.plot')
                    ->on('b.batchno', '=', 'harvest_summary.batchno');
            })
            ->leftJoin('user as mandor', 'harvest_summary.last_mandor_id', '=', 'mandor.userid')
            ->where('m.companycode', $companycode)
            ->where('m.isactive', 1)
            ->where('b.isactive', 1)
            ->whereNotNull('b.tanggalpanen')
            ->where('b.tanggalpanen', '<=', $date)
            ->select([
                'm.plot',
                'm.blok',
                'b.batchno',
                'b.batcharea',
                'b.tanggalpanen',
                'b.lifecyclestatus',
                'b.kodevarietas',
                DB::raw('COALESCE(harvest_summary.total_dipanen, 0) as total_dipanen'),
                DB::raw('(b.batcharea - COALESCE(harvest_summary.total_dipanen, 0)) as sisa'),
                DB::raw('CASE 
                    WHEN b.batcharea > 0 THEN (COALESCE(harvest_summary.total_dipanen, 0) / b.batcharea * 100)
                    ELSE 0 
                END as progress'),
                DB::raw('DATEDIFF("' . $date . '", b.tanggalpanen) + 1 as hari_panen'),
                'harvest_summary.last_harvest_date',
                'harvest_summary.last_mandor_id',
                'mandor.name as last_mandor_name',
                DB::raw('CASE 
                    WHEN harvest_summary.last_harvest_date = "' . $date . '" THEN "PANEN HARI INI"
                    WHEN harvest_summary.last_harvest_date IS NULL THEN "BELUM PERNAH PANEN"
                    WHEN COALESCE(harvest_summary.total_dipanen, 0) >= b.batcharea THEN "SELESAI"
                    ELSE "ONGOING"
                END as status_label')
            ])
            ->orderBy('m.blok')
            ->orderBy('m.plot');
    }
    
    /**
     * Calculate summary statistics
     */
    private function calculateSummary($data)
    {
        $totalPlots = $data->count();
        $totalLuasBatch = $data->sum('batcharea');
        $totalDipanen = $data->sum('total_dipanen');
        $totalSisa = $data->sum('sisa');
        $avgProgress = $totalPlots > 0 ? $data->avg('progress') : 0;
        
        $panenHariIni = $data->where('status_label', 'PANEN HARI INI')->count();
        $selesai = $data->where('status_label', 'SELESAI')->count();
        $ongoing = $data->where('status_label', 'ONGOING')->count();
        
        return [
            'total_plots' => $totalPlots,
            'total_luas_batch' => $totalLuasBatch,
            'total_dipanen' => $totalDipanen,
            'total_sisa' => $totalSisa,
            'avg_progress' => $avgProgress,
            'panen_hari_ini' => $panenHariIni,
            'selesai' => $selesai,
            'ongoing' => $ongoing
        ];
    }
    
    /**
     * Format saldo data for response
     */
    private function formatSaldoData($data)
    {
        return $data->map(function($item) {
            return [
                'blok' => $item->blok,
                'plot' => $item->plot,
                'batchno' => $item->batchno,
                'lifecycle' => $item->lifecyclestatus,
                'kodevarietas' => $item->kodevarietas,
                'tanggal_panen' => Carbon::parse($item->tanggalpanen)->format('d/m/Y'),
                'batcharea' => number_format((float)$item->batcharea, 2),
                'total_dipanen' => number_format((float)$item->total_dipanen, 2),
                'sisa' => number_format((float)$item->sisa, 2),
                'progress' => number_format((float)$item->progress, 1),
                'hari_panen' => (int)$item->hari_panen,
                'last_harvest_date' => $item->last_harvest_date ? Carbon::parse($item->last_harvest_date)->format('d/m/Y') : '-',
                'last_mandor_name' => $item->last_mandor_name ?? '-',
                'status_label' => $item->status_label
            ];
        })->values();
    }
    
    /**
     * Get company info
     */
    private function getCompanyInfo($companycode)
    {
        $company = DB::table('company')
            ->where('companycode', $companycode)
            ->first();
        
        return $company ? "{$company->companycode} - {$company->name}" : $companycode;
    }
}