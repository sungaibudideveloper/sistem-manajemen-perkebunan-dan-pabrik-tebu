<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

/**
 * GudangBbmController - FIXED: Use Session company consistently
 */
class GudangBbmController extends Controller
{
    /**
     * Display a listing of BBM orders ready for confirmation
     * ✅ FIXED: Use Session::get('companycode') instead of auth()->user()->companycode
     */
    public function index(Request $request)
    {
        try {
            // ✅ CRITICAL FIX: Read from SESSION, not from user table
            $companycode = Session::get('companycode');
            $user = auth()->user();
            
            $search = $request->input('search');
            $filterDate = $request->input('filter_date', now()->format('Y-m-d'));
            $showAll = $request->boolean('show_all');

            // ✅ FIXED: Use $companycode everywhere
            $query = DB::table('lkhdetailkendaraan as lk')
                ->join('lkhhdr as lkh', function($join) use ($companycode) {
                    $join->on('lk.companycode', '=', 'lkh.companycode')
                         ->on('lk.lkhno', '=', 'lkh.lkhno');
                })
                ->join('kendaraan as k', function($join) use ($companycode) {
                    $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                         ->where('k.companycode', '=', $companycode)
                         ->where('k.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                    $join->on('lk.operatorid', '=', 'tk.tenagakerjaid')
                         ->where('tk.companycode', '=', $companycode)
                         ->where('tk.isactive', '=', 1);
                })
                ->leftJoin('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->leftJoin('lkhdetailplot as ldp', function($join) {
                    $join->on('lk.companycode', '=', 'ldp.companycode')
                         ->on('lk.lkhno', '=', 'ldp.lkhno');
                })
                ->where('lk.companycode', $companycode)
                ->where('lk.status', 'PRINTED')
                ->whereNotNull('lk.ordernumber')
                ->whereNotNull('lk.solar')
                ->select([
                    'lk.id',
                    'lk.lkhno',
                    'lk.nokendaraan',
                    'lk.jammulai',
                    'lk.jamselesai',
                    'lk.hourmeterstart',
                    'lk.hourmeterend',
                    'lk.solar',
                    'lk.status',
                    'lk.ordernumber',
                    'lk.createdat',
                    'lk.gudangconfirm',
                    'lk.gudangconfirmedby',
                    'lk.gudangconfirmedat',
                    'lkh.lkhdate as lkh_date',
                    'lkh.activitycode',
                    'act.activityname',
                    'k.jenis',
                    'tk.nama as operator_nama',
                    DB::raw('GROUP_CONCAT(DISTINCT ldp.plot ORDER BY ldp.plot SEPARATOR ", ") as plots')
                ]);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('lk.lkhno', 'like', "%{$search}%")
                      ->orWhere('lk.nokendaraan', 'like', "%{$search}%")
                      ->orWhere('lk.ordernumber', 'like', "%{$search}%")
                      ->orWhere('tk.nama', 'like', "%{$search}%");
                });
            }

            // Apply date filter only when NOT show_all
            if ($filterDate && !$showAll) {
                $query->whereDate('lkh.lkhdate', $filterDate);
            }

            // Group by kendaraan
            $query->groupBy([
                'lk.id', 'lk.lkhno', 'lk.nokendaraan',
                'lk.jammulai', 'lk.jamselesai', 'lk.hourmeterstart',
                'lk.hourmeterend', 'lk.solar', 'lk.status',
                'lk.ordernumber', 'lk.createdat', 'lk.gudangconfirm',
                'lk.gudangconfirmedby', 'lk.gudangconfirmedat',
                'lkh.lkhdate', 'lkh.activitycode', 'act.activityname',
                'k.jenis', 'tk.nama'
            ]);

            $rawData = $query->orderBy('lkh.lkhdate', 'desc')
                             ->orderBy('lk.gudangconfirm')
                             ->orderBy('lk.lkhno')
                             ->orderBy('lk.nokendaraan')
                             ->get();

            // Group by order number for display
            $groupedData = $rawData->groupBy('ordernumber')->map(function($group) {
                $first = $group->first();
                $vehicles = $group->pluck('nokendaraan')->unique()->implode(', ');
                $totalSolar = $group->sum('solar');
                
                return (object) array_merge((array) $first, [
                    'vehicles' => $vehicles,
                    'vehicle_count' => $group->count(),
                    'total_solar' => $totalSolar
                ]);
            })->values();

            $page = (int) $request->input('page', 1);
            $perPage = 20; 
            $offset = ($page - 1) * $perPage;
            
            $bbmData = new \Illuminate\Pagination\LengthAwarePaginator(
                $groupedData->slice($offset, $perPage),
                $groupedData->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $stats = [
                'pending_confirm' => $groupedData->where('gudangconfirm', 0)->count(),
                'confirmed_today' => $this->getConfirmedTodayCount($companycode, $filterDate),
                'total_vehicles' => $rawData->count(),
                'total_solar' => $rawData->sum('solar')
            ];

            return view('transaction.gudang-bbm.index', compact('bbmData','stats','search','filterDate'))
                ->with([
                    'title' => 'Gudang BBM - Konfirmasi BBM',
                    'navbar' => 'Input Gudang BBM',
                    'nav' => 'BBM',
                ]);
        } catch (\Exception $e) {
            Log::error('Error in GudangBbmController@index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data BBM');
        }
    }

    /**
     * Show print view for specific Order Number
     * ✅ FIXED: Use Session company
     */
    public function show($ordernumber)
    {
        try {
            $companycode = Session::get('companycode');
            $user = auth()->user();
            
            // Get order data from lkhdetailkendaraan
            $orderData = DB::table('lkhdetailkendaraan')
                ->where('companycode', $companycode)
                ->where('ordernumber', $ordernumber)
                ->where('status', 'PRINTED')
                ->first();
                
            if (!$orderData) {
                return redirect()->route('transaction.gudang-bbm.index')
                    ->with('error', 'Order tidak ditemukan atau belum diprint');
            }
            
            // Get LKH header data
            $lkhData = DB::table('lkhhdr as lkh')
                ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
                ->leftJoin('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->where('lkh.companycode', $companycode)
                ->where('lkh.lkhno', $orderData->lkhno)
                ->select([
                    'lkh.*',
                    'u.name as mandor_nama',
                    'act.activityname'
                ])
                ->first();
                
            if (!$lkhData) {
                return redirect()->route('transaction.gudang-bbm.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }
            
            // Get vehicle data with plots via JOIN
            $vehicleData = DB::table('lkhdetailkendaraan as lk')
                ->join('kendaraan as k', function($join) use ($companycode) {
                    $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                         ->where('k.companycode', '=', $companycode)
                         ->where('k.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                    $join->on('lk.operatorid', '=', 'tk.tenagakerjaid')
                         ->where('tk.companycode', '=', $companycode)
                         ->where('tk.isactive', '=', 1);
                })
                ->leftJoin('lkhdetailplot as ldp', function($join) {
                    $join->on('lk.companycode', '=', 'ldp.companycode')
                         ->on('lk.lkhno', '=', 'ldp.lkhno');
                })
                ->where('lk.companycode', $companycode)
                ->where('lk.ordernumber', $ordernumber)
                ->where('lk.status', 'PRINTED')
                ->whereNotNull('lk.solar')
                ->select([
                    'lk.id',
                    'lk.nokendaraan',
                    'lk.jammulai',
                    'lk.jamselesai',
                    'lk.hourmeterstart',
                    'lk.hourmeterend',
                    'lk.solar',
                    'lk.ordernumber',
                    'lk.gudangconfirm',
                    'k.jenis',
                    'tk.nama as operator_nama',
                    DB::raw('GROUP_CONCAT(DISTINCT ldp.plot ORDER BY ldp.plot SEPARATOR ", ") as plots')
                ])
                ->groupBy([
                    'lk.id', 'lk.nokendaraan', 'lk.jammulai', 'lk.jamselesai',
                    'lk.hourmeterstart', 'lk.hourmeterend', 'lk.solar',
                    'lk.ordernumber', 'lk.gudangconfirm', 'k.jenis', 'tk.nama'
                ])
                ->orderBy('lk.nokendaraan')
                ->get();
                
            if ($vehicleData->isEmpty()) {
                return redirect()->route('transaction.gudang-bbm.index')
                    ->with('error', 'Tidak ada data kendaraan yang sudah diprint');
            }
            
            // Calculate totals
            $totalSolar = $vehicleData->sum('solar');
            $totalHourMeter = $vehicleData->sum(function($item) {
                return ($item->hourmeterend ?? 0) - ($item->hourmeterstart ?? 0);
            });
            
            $orderNumber = $ordernumber;
            $printDate = now()->format('d F Y, H:i:s');
            $canConfirm = $vehicleData->where('gudangconfirm', 0)->count() > 0;
            
            return view('transaction.gudang-bbm.show', compact(
                'lkhData',
                'vehicleData',
                'totalSolar',
                'totalHourMeter',
                'orderNumber',
                'printDate',
                'canConfirm'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error in GudangBbmController@show', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ordernumber' => $ordernumber
            ]);
            
            return redirect()->route('transaction.gudang-bbm.index')
                ->with('error', 'Terjadi kesalahan saat memuat halaman: ' . $e->getMessage());
        }
    }

    /**
     * Mark BBM order as confirmed (by Order Number)
     * ✅ FIXED: Use Session company
     */
    public function markConfirmed(Request $request, $ordernumber)
    {
        try {
            $request->validate([
                'ordernumber' => 'required|string'
            ]);
            
            $companycode = Session::get('companycode');
            $user = auth()->user();
            
            // Verify from lkhdetailkendaraan
            $orderExists = DB::table('lkhdetailkendaraan')
                ->where('companycode', $companycode)
                ->where('ordernumber', $ordernumber)
                ->where('status', 'PRINTED')
                ->exists();
                
            if (!$orderExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan atau belum diprint'
                ]);
            }
            
            DB::beginTransaction();
            
            // Update lkhdetailkendaraan
            $updated = DB::table('lkhdetailkendaraan')
                ->where('companycode', $companycode)
                ->where('ordernumber', $ordernumber)
                ->where('status', 'PRINTED')
                ->where('gudangconfirm', 0)
                ->update([
                    'gudangconfirm' => 1,
                    'gudangconfirmedby' => $user->name,
                    'gudangconfirmedat' => now(),
                    'adminupdateby' => $user->name,
                    'adminupdatedat' => now()
                ]);
            
            if ($updated === 0) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang bisa dikonfirmasi atau sudah dikonfirmasi sebelumnya'
                ]);
            }
            
            DB::commit();
            
            Log::info('BBM order confirmed', [
                'ordernumber' => $ordernumber,
                'total_vehicles' => $updated,
                'confirmed_by' => $user->name
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil mengkonfirmasi Order #{$ordernumber} ({$updated} kendaraan)"
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error in GudangBbmController@markConfirmed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ordernumber' => $ordernumber
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat konfirmasi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get count of confirmed orders today for statistics
     * ✅ FIXED: Use $companycode parameter
     */
    private function getConfirmedTodayCount($companycode, $filterDate)
    {
        $query = DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lkh', function($join) {
                $join->on('lk.companycode', '=', 'lkh.companycode')
                     ->on('lk.lkhno', '=', 'lkh.lkhno');
            })
            ->where('lk.companycode', $companycode)
            ->where('lk.status', 'PRINTED')
            ->where('lk.gudangconfirm', 1);
            
        if ($filterDate) {
            $query->whereDate('lkh.lkhdate', $filterDate);
        }
        
        return $query->distinct()->count('lk.ordernumber');
    }
}