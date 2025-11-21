<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuratJalanTimbanganReportController extends Controller
{
    public function index()
    {
        $companyCode = session('companycode');
        
        $title = 'Report Surat Jalan & Timbangan';
        $navbar = 'Report';
        $nav = 'Surat Jalan & Timbangan';

        return view('report.surat-jalan-timbangan.index', compact('title', 'navbar', 'nav'));
    }

    public function getData(Request $request)
    {
        try {
            $companyCode = session('companycode');
            
            // Parse date filters
            $startDate = $request->start_date ?: Carbon::today()->format('Y-m-d');
            $endDate = $request->end_date ?: Carbon::today()->format('Y-m-d');
            
            // Get filter options first
            $filterOptions = $this->getFilterOptions($companyCode, $startDate, $endDate);
            
            // Build query with filters
            $query = DB::table('suratjalanpos as sj')
                ->leftJoin('timbanganpayload as tp', function($join) {
                    $join->on('sj.companycode', '=', 'tp.companycode')
                         ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
                })
                ->leftJoin('user as mandor', function($join) {
                    $join->on('sj.mandorid', '=', 'mandor.userid')
                         ->where('mandor.idjabatan', '=', 5);
                })
                ->where('sj.companycode', $companyCode)
                ->whereBetween('sj.tanggalangkut', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            // Apply filters
            if ($request->filled('mandor')) {
                $query->where('sj.mandorid', $request->mandor);
            }
            if ($request->filled('plot')) {
                $query->where('sj.plot', $request->plot);
            }
            if ($request->filled('kontraktor')) {
                $query->where('sj.namakontraktor', $request->kontraktor);
            }
            if ($request->filled('nopol')) {
                $query->where('sj.nomorpolisi', $request->nopol);
            }
            if ($request->filled('status')) {
                if ($request->status === 'sudah') {
                    $query->whereNotNull('tp.suratjalanno');
                } elseif ($request->status === 'pending') {
                    $query->whereNull('tp.suratjalanno');
                }
            }

            // Get details
            $details = $query->select(
                'sj.suratjalanno',
                'sj.mandorid',
                'mandor.name as nama_mandor',
                'sj.plot',
                'sj.varietas',
                'sj.kategori',
                'sj.nomorpolisi',
                'sj.namasupir',
                'sj.namakontraktor',
                'sj.namasubkontraktor',
                'sj.tanggaltebang',
                'sj.tanggalangkut',
                'tp.bruto',
                'tp.netto',
                'tp.tgl1',
                'tp.jam1',
                'tp.tgl2',
                'tp.jam2',
                DB::raw('CASE WHEN tp.suratjalanno IS NULL THEN "Pending" ELSE "Sudah Timbang" END as status')
            )
            ->orderBy('sj.tanggalangkut', 'desc')
            ->get()
            ->map(function($item) {
                // Calculate waiting time
                $item->waktu_tunggu = null;
                if ($item->jam1 && $item->jam2) {
                    try {
                        $masuk = Carbon::parse($item->tgl1 . ' ' . $item->jam1);
                        $keluar = Carbon::parse($item->tgl2 . ' ' . $item->jam2);
                        $item->waktu_tunggu = $masuk->diffInMinutes($keluar);
                    } catch (\Exception $e) {
                        $item->waktu_tunggu = null;
                    }
                }
                return $item;
            });

            // Calculate summary
            $summary = [
                'total_sj' => $details->count(),
                'total_netto' => $details->whereNotNull('netto')->sum('netto'),
                'pending_timbangan' => $details->where('status', 'Pending')->count(),
                'avg_waktu_tunggu' => $details->whereNotNull('waktu_tunggu')->avg('waktu_tunggu'),
                'sudah_timbang' => $details->where('status', 'Sudah Timbang')->count(),
            ];

            // Hourly trend (accumulative netto per hour)
            $hourlyTrend = $this->getHourlyTrend($details);

            // Status breakdown
            $statusBreakdown = [
                ['name' => 'Sudah Timbang', 'value' => $summary['sudah_timbang']],
                ['name' => 'Pending', 'value' => $summary['pending_timbangan']],
            ];

            // Performance by Mandor
            $mandorPerformance = $details->groupBy('mandorid')->map(function($group) {
                return [
                    'mandorid' => $group->first()->mandorid,
                    'nama_mandor' => $group->first()->nama_mandor ?: $group->first()->mandorid,
                    'total_sj' => $group->count(),
                    'total_netto' => $group->whereNotNull('netto')->sum('netto'),
                ];
            })->sortByDesc('total_netto')->values()->take(10);

            // Vehicle performance
            $vehiclePerformance = $details->whereNotNull('netto')
                ->groupBy('nomorpolisi')
                ->map(function($group) {
                    $avgTime = $group->whereNotNull('waktu_tunggu')->avg('waktu_tunggu');
                    return [
                        'nopol' => $group->first()->nomorpolisi,
                        'trip_count' => $group->count(),
                        'total_netto' => $group->sum('netto'),
                        'avg_waktu_tunggu' => $avgTime ? round($avgTime, 0) : null,
                    ];
                })
                ->sortByDesc('trip_count')
                ->values()
                ->take(10);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'details' => $details,
                    'filterOptions' => $filterOptions,
                    'hourlyTrend' => $hourlyTrend,
                    'statusBreakdown' => $statusBreakdown,
                    'mandorPerformance' => $mandorPerformance,
                    'vehiclePerformance' => $vehiclePerformance,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getFilterOptions($companyCode, $startDate, $endDate)
    {
        // Get mandor list
        $mandors = DB::table('suratjalanpos as sj')
            ->leftJoin('user as mandor', function($join) {
                $join->on('sj.mandorid', '=', 'mandor.userid')
                     ->where('mandor.idjabatan', '=', 5);
            })
            ->where('sj.companycode', $companyCode)
            ->whereBetween('sj.tanggalangkut', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('sj.mandorid', 'mandor.name as nama_mandor')
            ->distinct()
            ->orderBy('sj.mandorid')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->mandorid,
                    'name' => $item->nama_mandor ?: $item->mandorid
                ];
            });

        // Get plots
        $plots = DB::table('suratjalanpos')
            ->where('companycode', $companyCode)
            ->whereBetween('tanggalangkut', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->distinct()
            ->pluck('plot')
            ->sort()
            ->values();

        // Get kontraktor
        $kontraktors = DB::table('suratjalanpos')
            ->where('companycode', $companyCode)
            ->whereBetween('tanggalangkut', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNotNull('namakontraktor')
            ->distinct()
            ->pluck('namakontraktor')
            ->sort()
            ->values();

        // Get nopol
        $nopols = DB::table('suratjalanpos')
            ->where('companycode', $companyCode)
            ->whereBetween('tanggalangkut', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNotNull('nomorpolisi')
            ->distinct()
            ->pluck('nomorpolisi')
            ->sort()
            ->values();

        return [
            'mandors' => $mandors,
            'plots' => $plots,
            'kontraktors' => $kontraktors,
            'nopols' => $nopols,
        ];
    }

    private function getHourlyTrend($details)
    {
        $timbangData = $details->whereNotNull('jam2')->sortBy('jam2');
        
        $hourlyData = [];
        for ($i = 0; $i <= 23; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $hourlyData[$hour] = 0;
        }

        foreach ($timbangData as $item) {
            if ($item->jam2) {
                $hour = substr($item->jam2, 0, 2) . ':00';
                if (isset($hourlyData[$hour])) {
                    $hourlyData[$hour] += $item->netto ?? 0;
                }
            }
        }

        // Make cumulative
        $cumulative = 0;
        foreach ($hourlyData as $hour => $value) {
            $cumulative += $value;
            $hourlyData[$hour] = $cumulative;
        }

        return collect($hourlyData)->map(function($value, $hour) {
            return ['hour' => $hour, 'netto' => $value];
        })->values();
    }
}