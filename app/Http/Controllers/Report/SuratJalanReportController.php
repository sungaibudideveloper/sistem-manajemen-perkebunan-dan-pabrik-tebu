<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuratJalanReportController extends Controller
{
    public function index()
    {
        $companyCode = session('companycode');
        
        $title = 'Report Surat Jalan';
        $navbar = 'Report';
        $nav = 'Surat Jalan';

        return view('report.surat-jalan.index', compact('title', 'navbar', 'nav'));
    }

    public function getData(Request $request)
    {
        try {
            $companyCode = session('companycode');
            
            // Parse date filter (default today)
            $tanggal = $request->tanggal ?: Carbon::today()->format('Y-m-d');
            
            // Get plot filter
            $filterPlot = $request->input('plot');
            
            // Get filter options first
            $filterOptions = $this->getFilterOptions($companyCode, $tanggal);
            
            // Build query
            $query = DB::table('suratjalanpos as sj')
                ->leftJoin('user as mandor', function($join) {
                    $join->on('sj.mandorid', '=', 'mandor.userid')
                        ->where('mandor.idjabatan', '=', 5);
                })
                ->leftJoin('kontraktor as k', function($join) {
                    $join->on('sj.namakontraktor', '=', 'k.id')
                        ->on('sj.companycode', '=', 'k.companycode');
                })
                ->leftJoin('subkontraktor as sk', function($join) {
                    $join->on('sj.namasubkontraktor', '=', 'sk.id')
                        ->on('sj.companycode', '=', 'sk.companycode');
                })
                ->where('sj.companycode', $companyCode)
                ->whereDate('sj.tanggalangkut', $tanggal);

            // Apply plot filter if selected
            if ($request->filled('plot')) {
                $query->where('sj.plot', $filterPlot);
            }

            // Get details
            $details = $query->select(
                'sj.companycode',
                'sj.suratjalanno',
                'sj.mandorid',
                'mandor.name as nama_mandor',
                'sj.plot',
                'sj.varietas',
                'sj.kategori',
                'sj.umur',
                'sj.kodetebang',
                'sj.langsir',
                'sj.tebusulit',
                'sj.kendaraankontraktor',
                'sj.nomorpolisi',
                'sj.namasupir',
                'sj.namakontraktor',
                'k.namakontraktor as nama_kontraktor_lengkap',
                'sj.namasubkontraktor',
                'sk.namasubkontraktor as nama_subkontraktor_lengkap',
                'sj.tanggalangkut',
                'sj.tanggalcetakpossecurity'
            )
            ->orderBy('sj.plot')
            ->orderBy('sj.tanggalangkut')
            ->get();

            // Calculate rit based on chronological order per nopol
            $ritCounter = [];
            $details = $details->map(function($item) use (&$ritCounter) {
                $nopol = $item->nomorpolisi;
                if (!isset($ritCounter[$nopol])) {
                    $ritCounter[$nopol] = 0;
                }
                $ritCounter[$nopol]++;
                $item->rit = $ritCounter[$nopol];
                return $item;
            });

            // Group by plot
            $groupedByPlot = $details->groupBy('plot')->map(function($group, $plot) {
                return [
                    'plot' => $plot,
                    'total_sj' => $group->count(),
                    'details' => $group->values()
                ];
            })->values();

            // Calculate summary
            $summary = [
                'total_sj' => $details->count(),
                'total_plot' => $details->pluck('plot')->unique()->count(),
                'langsir_count' => $details->where('langsir', 1)->count(),
                'tebu_sulit_count' => $details->where('tebusulit', 1)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'groupedByPlot' => $groupedByPlot,
                    'filterOptions' => $filterOptions,
                    'tanggal' => Carbon::parse($tanggal)->format('d M Y'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getFilterOptions($companyCode, $tanggal)
    {
        // Get plots for the selected date
        $plots = DB::table('suratjalanpos')
            ->where('companycode', $companyCode)
            ->whereDate('tanggalangkut', $tanggal)
            ->distinct()
            ->pluck('plot')
            ->sort()
            ->values();

        return [
            'plots' => $plots,
        ];
    }
}