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
            $filterBlok = $request->input('blok');
            $filterVarietas = $request->input('varietas');
            $filterLifecycle = $request->input('lifecycle');
            $filterPlottype = $request->input('plottype');
            $filterPkp = $request->input('pkp');
            $filterAgeMin = $request->input('age_min');
            $filterAgeMax = $request->input('age_max');

            // Base query
            $query = DB::table('masterlist as m')
                ->join('batch as b', 'm.activebatchno', '=', 'b.batchno')
                ->where('m.companycode', $companycode)
                ->where('m.isactive', 1)
                ->where('b.isactive', 1);

            // Apply filters
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
                    'm.plot',
                    'm.blok',
                    'b.batchno',
                    'b.batcharea',
                    'b.batchdate',
                    'b.lifecyclestatus',
                    'b.kodevarietas',
                    'b.pkp',
                    'b.plottype',
                    'b.tanggalpanen',
                    'b.kontraktorid',
                    DB::raw('DATEDIFF(CURDATE(), b.batchdate) as age_days')
                ])
                ->orderBy('m.blok')
                ->orderBy('m.plot')
                ->get();

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

            // Get filter options (for dropdowns)
            $filterOptions = [
                'bloks' => DB::table('masterlist')
                    ->where('companycode', $companycode)
                    ->where('isactive', 1)
                    ->whereNotNull('blok')
                    ->distinct()
                    ->orderBy('blok')
                    ->pluck('blok'),
                    
                'varietas' => DB::table('batch')
                    ->where('companycode', $companycode)
                    ->where('isactive', 1)
                    ->whereNotNull('kodevarietas')
                    ->distinct()
                    ->orderBy('kodevarietas')
                    ->pluck('kodevarietas'),
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
                'young' => $detailData->where('age_days', '<=', 90)->count(), // 0-90 days
                'growing' => $detailData->whereBetween('age_days', [91, 180])->count(), // 91-180 days
                'mature' => $detailData->whereBetween('age_days', [181, 365])->count(), // 181-365 days
                'overdue' => $detailData->where('age_days', '>', 365)->count(), // >365 days
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