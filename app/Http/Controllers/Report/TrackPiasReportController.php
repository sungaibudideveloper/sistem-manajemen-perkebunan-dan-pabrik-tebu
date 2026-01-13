<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class TrackPiasReportController extends Controller
{
    /**
     * Display Track Pias Report
     */
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        
        // Generate years (2020 - current year + 1)
        $currentYear = (int)date('Y');
        $years = range(2020, $currentYear + 1);
        $years = array_reverse($years); // Descending order
        
        // Get all bloks
        $bloks = DB::table('masterlist')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->distinct()
            ->orderBy('blok')
            ->pluck('blok');
        
        return view('report.track-pias.index', [
            'title' => 'Report Track Pias',
            'navbar' => 'Report',
            'nav' => 'Track Pias',
            'years' => $years,
            'bloks' => $bloks,
            'currentYear' => $currentYear
        ]);
    }
    
    /**
     * Get Track Pias Data (API endpoint)
     */
    public function getData(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $year = $request->input('year', date('Y'));
            $bloks = $request->input('bloks', []); // Array of selected bloks
            
            if (empty($bloks)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih minimal 1 blok'
                ], 400);
            }
            
            // Get plot data with active batches
            $plots = DB::table('masterlist as m')
                ->join('batch as b', function($join) use ($companycode) {
                    $join->on('m.activebatchno', '=', 'b.batchno')
                        ->where('b.companycode', '=', $companycode)
                        ->where('b.isactive', '=', 1);
                })
                ->where('m.companycode', $companycode)
                ->where('m.isactive', 1)
                ->whereIn('m.blok', $bloks)
                ->select([
                    'm.blok',
                    'm.plot',
                    'b.batchno',
                    'b.lifecyclestatus',
                    'b.kodevarietas',
                    'b.tanggalpanen'
                ])
                ->orderBy('m.blok')
                ->orderBy('m.plot')
                ->get();
            
            // Get all Pias activities for the year
            $startDate = "{$year}-01-01";
            $endDate = "{$year}-12-31";
            
            $piasActivities = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', function($join) use ($companycode) {
                    $join->on('ldp.lkhno', '=', 'lh.lkhno')
                        ->on('ldp.companycode', '=', 'lh.companycode');
                })
                ->where('ldp.companycode', $companycode)
                ->where('lh.activitycode', '5.2.1') // Hardcoded Pias activity
                ->where('lh.approvalstatus', '1') // Only approved
                ->whereBetween('lh.lkhdate', [$startDate, $endDate])
                ->whereIn('ldp.plot', $plots->pluck('plot'))
                ->select([
                    'ldp.plot',
                    'ldp.batchno',
                    'lh.lkhdate',
                    'lh.mandorid',
                    'ldp.luashasil'
                ])
                ->orderBy('lh.lkhdate')
                ->get();
            
            // Process data: group by plot and month, determine RON1 and RON2
            $trackData = $this->processTrackData($plots, $piasActivities, $year);
            
            return response()->json([
                'success' => true,
                'year' => $year,
                'data' => $trackData,
                'summary' => $this->calculateSummary($trackData)
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error getting Track Pias data: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process track data: assign RON1 and RON2 based on first occurrence in month
     */
    private function processTrackData($plots, $activities, $year)
    {
        $result = [];
        
        // Group activities by plot and month
        $activitiesByPlot = [];
        foreach ($activities as $activity) {
            $plotKey = $activity->plot;
            $month = Carbon::parse($activity->lkhdate)->format('Y-m');
            
            if (!isset($activitiesByPlot[$plotKey])) {
                $activitiesByPlot[$plotKey] = [];
            }
            
            if (!isset($activitiesByPlot[$plotKey][$month])) {
                $activitiesByPlot[$plotKey][$month] = [];
            }
            
            $activitiesByPlot[$plotKey][$month][] = $activity;
        }
        
        // Build result structure
        foreach ($plots as $plot) {
            $plotKey = $plot->plot;
            $monthsData = [];
            
            // For each month (1-12)
            for ($m = 1; $m <= 12; $m++) {
                $monthKey = sprintf('%d-%02d', $year, $m);
                $monthActivities = $activitiesByPlot[$plotKey][$monthKey] ?? [];
                
                $ron1 = null;
                $ron2 = null;
                
                if (!empty($monthActivities)) {
                    // First occurrence = RON1
                    $ron1 = Carbon::parse($monthActivities[0]->lkhdate)->format('d');
                    
                    // If more than 1 occurrence, last one = RON2
                    if (count($monthActivities) > 1) {
                        $ron2 = Carbon::parse($monthActivities[count($monthActivities) - 1]->lkhdate)->format('d');
                    }
                }
                
                $monthsData[] = [
                    'month' => $m,
                    'month_name' => Carbon::create($year, $m, 1)->format('M'),
                    'ron1' => $ron1,
                    'ron2' => $ron2,
                    'count' => count($monthActivities)
                ];
            }
            
            $result[] = [
                'blok' => $plot->blok,
                'plot' => $plot->plot,
                'batchno' => $plot->batchno,
                'lifecycle' => $plot->lifecyclestatus,
                'varietas' => $plot->kodevarietas,
                'tanggal_panen' => $plot->tanggalpanen ? Carbon::parse($plot->tanggalpanen)->format('d/m/Y') : '-',
                'months' => $monthsData
            ];
        }
        
        return $result;
    }
    
    /**
     * Calculate summary statistics
     */
    private function calculateSummary($data)
    {
        $totalPlots = count($data);
        $totalRON1 = 0;
        $totalRON2 = 0;
        $monthlyCompletion = array_fill(1, 12, ['ron1' => 0, 'ron2' => 0]);
        
        foreach ($data as $plot) {
            foreach ($plot['months'] as $month) {
                if ($month['ron1']) {
                    $totalRON1++;
                    $monthlyCompletion[$month['month']]['ron1']++;
                }
                if ($month['ron2']) {
                    $totalRON2++;
                    $monthlyCompletion[$month['month']]['ron2']++;
                }
            }
        }
        
        return [
            'total_plots' => $totalPlots,
            'total_ron1' => $totalRON1,
            'total_ron2' => $totalRON2,
            'completion_rate_ron1' => $totalPlots > 0 ? round(($totalRON1 / ($totalPlots * 12)) * 100, 1) : 0,
            'completion_rate_ron2' => $totalPlots > 0 ? round(($totalRON2 / ($totalPlots * 12)) * 100, 1) : 0,
            'monthly_completion' => $monthlyCompletion
        ];
    }
}