<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class MasterLahanReportController extends Controller
{
    /**
     * Display the master lahan report page
     */
    public function index()
    {
        return view('report.manajemen-lahan.index', [
            'title' => 'Lifecycle Status Dashboard',
            'navbar' => 'Report',
            'nav' => 'Master Lahan Report'
        ]);
    }

    /**
     * Get report data via AJAX
     */
    public function getData(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Get filters from request
            $filterGroup = $request->input('group');
            $filterBlok = $request->input('blok');
            $filterVarietas = $request->input('varietas');
            $filterLifecycle = $request->input('lifecycle');
            $filterPlottype = $request->input('plottype');
            $filterPkp = $request->input('pkp');
            $filterAgeMin = $request->input('age_min');
            $filterAgeMax = $request->input('age_max');

            // Base query
            $query = DB::table('masterlist as m')
                ->join('batch as b', function($join) {
                    $join->on('m.activebatchno', '=', 'b.batchno')
                        ->on('m.companycode', '=', 'b.companycode');
                })
                ->where('m.isactive', 1)
                ->where('b.isactive', 1);

            // Apply group filter
            if ($filterGroup === 'all-tbl') {
                $query->whereIn('m.companycode', ['TBL1', 'TBL2', 'TBL3']);
            } elseif ($filterGroup === 'all-divisi') {
                // No company filter - show all
            } else {
                // Default: current session company only
                $query->where('m.companycode', $companycode);
            }

            // Apply other filters
            if ($filterBlok) {
                $query->where('m.blok', $filterBlok);
            }
            if ($filterVarietas) {
                $query->where('b.kodevarietas', $filterVarietas);
            }
            if ($filterLifecycle) {
                $query->where('b.lifecyclestatus', $filterLifecycle);
            }
            if ($filterPlottype) {
                $query->where('b.plottype', $filterPlottype);
            }
            if ($filterPkp) {
                $query->where('b.pkp', $filterPkp);
            }
            if ($filterAgeMin) {
                $query->whereRaw('DATEDIFF(CURDATE(), b.batchdate) >= ?', [$filterAgeMin]);
            }
            if ($filterAgeMax) {
                $query->whereRaw('DATEDIFF(CURDATE(), b.batchdate) <= ?', [$filterAgeMax]);
            }

            // Get detailed data
            $detailData = $query
                ->select([
                    'm.companycode',
                    'm.plot',
                    'm.blok',
                    'b.batchno',
                    'b.batcharea',
                    'b.batchdate',
                    'b.tanggalulangtahun',
                    'b.lifecyclestatus',
                    'b.kodevarietas',
                    'b.pkp',
                    'b.plottype',
                    'b.tanggalpanen',
                    'b.kontraktorid',
                    DB::raw('DATEDIFF(CURDATE(), b.tanggalulangtahun) as age_days'),
                    DB::raw('ROUND(DATEDIFF(CURDATE(), b.tanggalulangtahun) / 30.44) as age_months')
                ])
                ->orderBy('m.companycode')
                ->orderBy('m.blok')
                ->orderBy('m.plot')
                ->get();

            $detailData = $detailData->map(function($item) {
                $item->companycode_formatted = formatCompanyCode($item->companycode);
                return $item;
            });

            // Calculate summary statistics
            $summary = [
                'total_plots' => $detailData->count(),
                'total_area' => $detailData->sum('batcharea'),
                'pc_count' => $detailData->where('lifecyclestatus', 'PC')->count(),
                'rc1_count' => $detailData->where('lifecyclestatus', 'RC1')->count(),
                'rc2_count' => $detailData->where('lifecyclestatus', 'RC2')->count(),
                'rc3_count' => $detailData->where('lifecyclestatus', 'RC3')->count(),
                'pc_area' => $detailData->where('lifecyclestatus', 'PC')->sum('batcharea'),
                'rc1_area' => $detailData->where('lifecyclestatus', 'RC1')->sum('batcharea'),
                'rc2_area' => $detailData->where('lifecyclestatus', 'RC2')->sum('batcharea'),
                'rc3_area' => $detailData->where('lifecyclestatus', 'RC3')->sum('batcharea'),
            ];

            // Get filter options (for dropdowns) - adjust based on group filter
            $filterOptionsQuery = DB::table('masterlist as m')
                ->join('batch as b', function($join) {
                    $join->on('m.activebatchno', '=', 'b.batchno')
                        ->on('m.companycode', '=', 'b.companycode');
                })
                ->where('m.isactive', 1)
                ->where('b.isactive', 1);

            // Apply same group filter for filter options
            if ($filterGroup === 'all-tbl') {
                $filterOptionsQuery->whereIn('m.companycode', ['TBL1', 'TBL2', 'TBL3']);
            } elseif ($filterGroup === 'all-divisi') {
                // No filter
            } else {
                $filterOptionsQuery->where('m.companycode', $companycode);
            }

            $filterOptions = [
                'bloks' => (clone $filterOptionsQuery)
                    ->whereNotNull('m.blok')
                    ->distinct()
                    ->orderBy('m.blok')
                    ->pluck('m.blok'),
                    
                'varietas' => (clone $filterOptionsQuery)
                    ->whereNotNull('b.kodevarietas')
                    ->distinct()
                    ->orderBy('b.kodevarietas')
                    ->pluck('b.kodevarietas'),
            ];

            // Lifecycle distribution for chart
            $lifecycleChart = [
                ['name' => 'PC', 'value' => $summary['pc_count'], 'area' => $summary['pc_area']],
                ['name' => 'RC1', 'value' => $summary['rc1_count'], 'area' => $summary['rc1_area']],
                ['name' => 'RC2', 'value' => $summary['rc2_count'], 'area' => $summary['rc2_area']],
                ['name' => 'RC3', 'value' => $summary['rc3_count'], 'area' => $summary['rc3_area']],
            ];

            // Varietas distribution
            $varietasChart = $detailData
                ->groupBy('kodevarietas')
                ->map(function($group) {
                    return [
                        'name' => $group->first()->kodevarietas ?? 'Unknown',
                        'plots' => $group->count(),
                        'area' => $group->sum('batcharea')
                    ];
                })
                ->values();

            // Plot type distribution for chart
            $plottypeChart = [
                ['name' => 'KBD', 'value' => $detailData->where('plottype', 'KBD')->count(), 'area' => $detailData->where('plottype', 'KBD')->sum('batcharea')],
                ['name' => 'KTG', 'value' => $detailData->where('plottype', 'KTG')->count(), 'area' => $detailData->where('plottype', 'KTG')->sum('batcharea')],
            ];

            // Age distribution
            $ageDistribution = [
                'young' => $detailData->where('age_months', '<=', 3)->count(),      // 0-3 bulan
                'growing' => $detailData->whereBetween('age_months', [4, 6])->count(),  // 4-6 bulan
                'mature' => $detailData->whereBetween('age_months', [7, 12])->count(),  // 7-12 bulan
                'overdue' => $detailData->where('age_months', '>', 12)->count(),     // >12 bulan
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'details' => $detailData,
                    'filterOptions' => $filterOptions,
                    'lifecycleChart' => $lifecycleChart,
                    'varietasChart' => $varietasChart,
                    'plottypeChart' => $plottypeChart,
                    'ageDistribution' => $ageDistribution,
                ],
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            \Log::error("Master Lahan Report Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
}