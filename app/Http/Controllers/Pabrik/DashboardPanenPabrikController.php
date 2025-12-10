<?php

namespace App\Http\Controllers\Pabrik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardPanenPabrikController extends Controller
{
    const PANEN_ACTIVITIES = ['4.3.3', '4.4.3', '4.5.2'];
    
    public function index()
    {
        $title = 'Dashboard Panen Pabrik';
        $navbar = 'Dashboard';
        $nav = 'Panen Pabrik';

        return view('pabrik.dashboard-panen-pabrik.index', compact('title', 'navbar', 'nav'));
    }

    public function getData(Request $request)
    {
        try {
            $tahun = $request->input('tahun', Carbon::now()->year);
            
            // Get periode panen (first panen date + 6 months)
            $periodeInfo = $this->getPeriodePanen($tahun);
            
            if (!$periodeInfo['has_data']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'has_data' => false,
                        'message' => 'Belum ada data panen untuk tahun ' . $tahun,
                        'tahun' => $tahun
                    ]
                ]);
            }
            
            $startDate = $periodeInfo['start_date'];
            $endDate = $periodeInfo['end_date'];
            
            // Get all companies
            $companies = $this->getActiveCompanies();
            
            // Build summary per company
            $companySummary = [];
            $totalTargetAll = 0;
            $totalProgressAll = 0;
            $totalRitAll = 0;
            $totalTonaseAll = 0;
            
            foreach ($companies as $company) {
                $companyCode = $company->companycode;
                
                // Get target luas (from active batches)
                $target = $this->getTargetLuas($companyCode);
                
                // Get progress luas (approved panen LKH in periode)
                $progress = $this->getProgressLuas($companyCode, $startDate, $endDate);
                
                // Get rit & tonase
                $ritData = $this->getRitTonase($companyCode, $startDate, $endDate);
                
                // Get status timbangan
                $statusTimbangan = $this->getStatusTimbangan($companyCode, $startDate, $endDate);
                
                $percentage = $target > 0 ? round(($progress / $target) * 100, 1) : 0;
                
                $companySummary[] = [
                    'companycode' => $companyCode,
                    'companyname' => formatCompanyCode($companyCode),
                    'target' => $target,
                    'progress' => $progress,
                    'percentage' => $percentage,
                    'total_rit' => $ritData['total_rit'],
                    'sudah_timbang' => $ritData['sudah_timbang'],
                    'pending_timbang' => $ritData['pending_timbang'],
                    'total_tonase' => $ritData['total_tonase'],
                    'status_breakdown' => $statusTimbangan
                ];
                
                $totalTargetAll += $target;
                $totalProgressAll += $progress;
                $totalRitAll += $ritData['total_rit'];
                $totalTonaseAll += $ritData['total_tonase'];
            }
            
            // Get trash data
            $trashData = $this->getTrashData($startDate, $endDate);
            
            // Get trend data
            $trendData = $this->getTrendData($startDate, $endDate);
            
            // Get daily performance
            $dailyPerformance = $this->getDailyPerformance($startDate, $endDate);
            
            $overallPercentage = $totalTargetAll > 0 ? round(($totalProgressAll / $totalTargetAll) * 100, 1) : 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'has_data' => true,
                    'tahun' => $tahun,
                    'periode' => [
                        'start' => Carbon::parse($startDate)->format('d M Y'),
                        'end' => Carbon::parse($endDate)->format('d M Y'),
                        'start_raw' => $startDate,
                        'end_raw' => $endDate
                    ],
                    'summary' => [
                        'total_target' => $totalTargetAll,
                        'total_progress' => $totalProgressAll,
                        'overall_percentage' => $overallPercentage,
                        'total_rit' => $totalRitAll,
                        'total_tonase' => $totalTonaseAll
                    ],
                    'companies' => $companySummary,
                    'trash' => $trashData,
                    'trend' => $trendData,
                    'daily_performance' => $dailyPerformance
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Dashboard pabrik error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getPeriodePanen($tahun)
    {
        // Find first panen date in the year
        $firstPanen = DB::table('batch')
            ->whereYear('tanggalpanen', $tahun)
            ->whereNotNull('tanggalpanen')
            ->orderBy('tanggalpanen', 'asc')
            ->value('tanggalpanen');
        
        if (!$firstPanen) {
            return ['has_data' => false];
        }
        
        $startDate = Carbon::parse($firstPanen)->startOfMonth();
        $endDate = $startDate->copy()->addMonths(6)->endOfMonth();
        
        return [
            'has_data' => true,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ];
    }
    
    private function getActiveCompanies()
    {
        return DB::table('company')
            ->orderBy('companycode')
            ->get();
    }
    
    private function getTargetLuas($companyCode)
    {
        return DB::table('batch')
            ->where('companycode', $companyCode)
            ->where('isactive', 1)
            ->sum('batcharea') ?? 0;
    }
    
    private function getProgressLuas($companyCode, $startDate, $endDate)
    {
        return DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->join('batch as b', function($join) {
                $join->on('ldp.batchno', '=', 'b.batchno')
                    ->on('ldp.companycode', '=', 'b.companycode');
            })
            ->where('ldp.companycode', $companyCode)
            ->where('lh.approvalstatus', '1')
            ->whereIn('lh.activitycode', self::PANEN_ACTIVITIES)
            ->whereBetween('b.tanggalpanen', [$startDate, $endDate])
            ->sum('ldp.luashasil') ?? 0;
    }
    
    private function getRitTonase($companyCode, $startDate, $endDate)
    {
        $ritQuery = DB::table('suratjalanpos as sj')
            ->leftJoin('timbanganpayload as tp', function($join) {
                $join->on('sj.companycode', '=', 'tp.companycode')
                    ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
            })
            ->join('batch as b', function($join) {
                $join->on('sj.companycode', '=', 'b.companycode')
                    ->on('sj.plot', '=', 'b.plot')
                    ->where('b.isactive', '=', 1);
            })
            ->where('sj.companycode', $companyCode)
            ->whereBetween('b.tanggalpanen', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(sj.suratjalanno) as total_rit'),
                DB::raw('SUM(CASE WHEN tp.netto IS NOT NULL THEN 1 ELSE 0 END) as sudah_timbang'),
                DB::raw('SUM(CASE WHEN tp.netto IS NULL THEN 1 ELSE 0 END) as pending_timbang'),
                DB::raw('SUM(tp.netto) as total_tonase')
            )
            ->first();
        
        return [
            'total_rit' => $ritQuery->total_rit ?? 0,
            'sudah_timbang' => $ritQuery->sudah_timbang ?? 0,
            'pending_timbang' => $ritQuery->pending_timbang ?? 0,
            'total_tonase' => $ritQuery->total_tonase ?? 0
        ];
    }
    
    private function getStatusTimbangan($companyCode, $startDate, $endDate)
    {
        $data = DB::table('suratjalanpos as sj')
            ->leftJoin('timbanganpayload as tp', function($join) {
                $join->on('sj.companycode', '=', 'tp.companycode')
                    ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
            })
            ->join('batch as b', function($join) {
                $join->on('sj.companycode', '=', 'b.companycode')
                    ->on('sj.plot', '=', 'b.plot')
                    ->where('b.isactive', '=', 1);
            })
            ->where('sj.companycode', $companyCode)
            ->whereBetween('b.tanggalpanen', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(CASE WHEN tp.netto IS NOT NULL THEN 1 END) as sudah'),
                DB::raw('COUNT(CASE WHEN tp.netto IS NULL THEN 1 END) as pending')
            )
            ->first();
        
        return [
            ['name' => 'Sudah Timbang', 'value' => $data->sudah ?? 0],
            ['name' => 'Pending', 'value' => $data->pending ?? 0]
        ];
    }
    
    private function getTrashData($startDate, $endDate)
    {
        $trashStats = DB::table('trash as t')
            ->join('suratjalanpos as sj', function($join) {
                $join->on('t.suratjalanno', '=', 'sj.suratjalanno')
                    ->on('t.companycode', '=', 'sj.companycode');
            })
            ->join('batch as b', function($join) {
                $join->on('sj.companycode', '=', 'b.companycode')
                    ->on('sj.plot', '=', 'b.plot')
                    ->where('b.isactive', '=', 1);
            })
            ->whereBetween('b.tanggalpanen', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(t.suratjalanno) as total_sampling'),
                DB::raw('AVG(t.total) as avg_trash_pct'),
                DB::raw('AVG(t.nettotrash) as avg_netto_trash'),
                DB::raw('SUM(CASE WHEN t.total > t.toleransi THEN 1 ELSE 0 END) as above_tolerance'),
                DB::raw('COUNT(t.suratjalanno) as total_count')
            )
            ->first();
        
        // Breakdown per company
        $trashByCompany = DB::table('trash as t')
            ->join('suratjalanpos as sj', function($join) {
                $join->on('t.suratjalanno', '=', 'sj.suratjalanno')
                    ->on('t.companycode', '=', 'sj.companycode');
            })
            ->join('batch as b', function($join) {
                $join->on('sj.companycode', '=', 'b.companycode')
                    ->on('sj.plot', '=', 'b.plot')
                    ->where('b.isactive', '=', 1);
            })
            ->whereBetween('b.tanggalpanen', [$startDate, $endDate])
            ->groupBy('t.companycode')
            ->select(
                't.companycode',
                DB::raw('COUNT(t.suratjalanno) as total_sampling'),
                DB::raw('AVG(t.total) as avg_trash_pct')
            )
            ->get()
            ->map(function($item) {
                return [
                    'company' => formatCompanyCode($item->companycode),
                    'sampling' => $item->total_sampling,
                    'avg_trash' => round($item->avg_trash_pct, 2)
                ];
            });
        
        $aboveTolerancePct = ($trashStats->total_count > 0) 
            ? round(($trashStats->above_tolerance / $trashStats->total_count) * 100, 1) 
            : 0;
        
        return [
            'total_sampling' => $trashStats->total_sampling ?? 0,
            'avg_trash_percentage' => round($trashStats->avg_trash_pct ?? 0, 2),
            'avg_netto_trash' => round($trashStats->avg_netto_trash ?? 0, 2),
            'above_tolerance_count' => $trashStats->above_tolerance ?? 0,
            'above_tolerance_pct' => $aboveTolerancePct,
            'by_company' => $trashByCompany
        ];
    }
    
    private function getTrendData($startDate, $endDate)
    {
        // Monthly trend
        $monthlyTrend = DB::table('suratjalanpos as sj')
            ->leftJoin('timbanganpayload as tp', function($join) {
                $join->on('sj.companycode', '=', 'tp.companycode')
                    ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
            })
            ->join('batch as b', function($join) {
                $join->on('sj.companycode', '=', 'b.companycode')
                    ->on('sj.plot', '=', 'b.plot')
                    ->where('b.isactive', '=', 1);
            })
            ->whereBetween('b.tanggalpanen', [$startDate, $endDate])
            ->select(
                'sj.companycode',
                DB::raw('DATE_FORMAT(sj.tanggalangkut, "%Y-%m") as month'),
                DB::raw('COUNT(sj.suratjalanno) as total_rit'),
                DB::raw('SUM(tp.netto) as total_tonase')
            )
            ->groupBy('sj.companycode', 'month')
            ->orderBy('month')
            ->get()
            ->groupBy('companycode')
            ->map(function($items, $company) {
                return [
                    'company' => formatCompanyCode($company),
                    'data' => $items->map(function($item) {
                        return [
                            'month' => Carbon::parse($item->month . '-01')->format('M Y'),
                            'rit' => $item->total_rit,
                            'tonase' => round($item->total_tonase / 1000, 2)
                        ];
                    })->values()
                ];
            })
            ->values();
        
        return [
            'monthly' => $monthlyTrend
        ];
    }
    
    private function getDailyPerformance($startDate, $endDate)
    {
        $last7Days = Carbon::parse($endDate)->subDays(6)->format('Y-m-d');
        
        $daily = DB::table('suratjalanpos as sj')
            ->leftJoin('timbanganpayload as tp', function($join) {
                $join->on('sj.companycode', '=', 'tp.companycode')
                    ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
            })
            ->join('batch as b', function($join) {
                $join->on('sj.companycode', '=', 'b.companycode')
                    ->on('sj.plot', '=', 'b.plot')
                    ->where('b.isactive', '=', 1);
            })
            ->whereBetween('sj.tanggalangkut', [$last7Days . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(sj.tanggalangkut) as date'),
                DB::raw('COUNT(sj.suratjalanno) as total_rit'),
                DB::raw('SUM(tp.netto) as total_tonase')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M'),
                    'rit' => $item->total_rit,
                    'tonase' => round($item->total_tonase / 1000, 2)
                ];
            });
        
        return $daily;
    }
}