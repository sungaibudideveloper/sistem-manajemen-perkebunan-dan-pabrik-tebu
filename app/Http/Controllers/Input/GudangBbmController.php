<?php

namespace App\Http\Controllers\Input;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GudangBbmController extends Controller
{
    /**
     * Display a listing of BBM orders ready for confirmation
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $search = $request->input('search');
            $filterDate = $request->input('filter_date', now()->format('Y-m-d'));
            $showAll = $request->boolean('show_all');

            $query = DB::table('kendaraanbbm as kb')
                ->join('lkhhdr as lkh', function($join) {
                    $join->on('kb.companycode', '=', 'lkh.companycode')
                         ->on('kb.lkhno', '=', 'lkh.lkhno');
                })
                ->join('kendaraan as k', function($join) use ($user) {
                    $join->on('kb.nokendaraan', '=', 'k.nokendaraan')
                         ->where('k.companycode', '=', $user->companycode)
                         ->where('k.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk', function($join) use ($user) {
                    $join->on('kb.operatorid', '=', 'tk.tenagakerjaid')
                         ->where('tk.companycode', '=', $user->companycode)
                         ->where('tk.isactive', '=', 1);
                })
                ->leftJoin('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->where('kb.companycode', $user->companycode)
                ->where('lkh.mobile_status', 'COMPLETED')
                ->where('kb.status', 'PRINTED')
                ->whereNotNull('kb.solar')
                ->select([
                    'kb.lkhno','kb.plot','kb.nokendaraan','kb.jammulai','kb.jamselesai',
                    'kb.hourmeterstart','kb.hourmeterend','kb.solar','kb.status','kb.ordernumber','kb.createdat',
                    'kb.gudangconfirm','kb.gudangconfirmedby','kb.gudangconfirmedat',
                    'lkh.lkhdate as lkh_date','lkh.activitycode','act.activityname',
                    'k.jenis','tk.nama as operator_nama'
                ]);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('kb.lkhno', 'like', "%{$search}%")
                      ->orWhere('kb.nokendaraan', 'like', "%{$search}%")
                      ->orWhere('kb.ordernumber', 'like', "%{$search}%")
                      ->orWhere('tk.nama', 'like', "%{$search}%");
                });
            }

            // Apply date filter only when NOT show_all
            if ($filterDate && !$showAll) {
                $query->whereDate('lkh.lkhdate', $filterDate);
            }

            $rawData = $query->orderBy('lkh.lkhdate', 'desc')
                             ->orderBy('kb.gudangconfirm')
                             ->orderBy('kb.lkhno')
                             ->orderBy('kb.nokendaraan')
                             ->get();

            $groupedData = $rawData->groupBy(function($item) {
                return $item->lkhno . '-' . $item->nokendaraan;
            })->map(function($group) {
                $first = $group->first();
                $plots = $group->pluck('plot')->implode(', ');
                return (object) array_merge((array) $first, [
                    'plots' => $plots,
                    'plot_count' => $group->count()
                ]);
            })->values();

            $page = (int) $request->input('page', 1);
            $perPage = 20; $offset = ($page - 1) * $perPage;
            $bbmData = new \Illuminate\Pagination\LengthAwarePaginator(
                $groupedData->slice($offset, $perPage),
                $groupedData->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $stats = [
                'pending_confirm' => $groupedData->where('gudangconfirm', 0)->count(),
                'confirmed_today' => $this->getConfirmedTodayCount($user->companycode, $filterDate),
                'total_vehicles' => $groupedData->unique('nokendaraan')->count(),
                'total_solar' => $groupedData->sum('solar')
            ];

            return view('input.gudang.gudang-bbm', compact('bbmData','stats','search','filterDate'))
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
     */
    public function show($ordernumber)
    {
        try {
            $user = auth()->user();
            
            // Get LKH header data based on order number
            $orderData = DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('ordernumber', $ordernumber)
                ->where('status', 'PRINTED')
                ->first();
                
            if (!$orderData) {
                return redirect()->route('input.gudang-bbm.index')
                    ->with('error', 'Order tidak ditemukan atau belum diprint');
            }
            
            // Get LKH header data using LKH from order
            $lkhData = DB::table('lkhhdr as lkh')
                ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
                ->leftJoin('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->where('lkh.companycode', $user->companycode)
                ->where('lkh.lkhno', $orderData->lkhno)
                ->where('lkh.mobile_status', 'COMPLETED')
                ->select([
                    'lkh.*',
                    'u.name as mandor_nama',
                    'act.activityname'
                ])
                ->first();
                
            if (!$lkhData) {
                return redirect()->route('input.gudang-bbm.index')
                    ->with('error', 'Data LKH tidak ditemukan atau belum completed');
            }
            
            // Get vehicle data for this Order Number (PRINTED status only)
            $vehicleData = DB::table('kendaraanbbm as kb')
                ->join('kendaraan as k', function($join) use ($user) {
                    $join->on('kb.nokendaraan', '=', 'k.nokendaraan')
                         ->where('k.companycode', '=', $user->companycode)
                         ->where('k.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk', function($join) use ($user) {
                    $join->on('kb.operatorid', '=', 'tk.tenagakerjaid')
                         ->where('tk.companycode', '=', $user->companycode)
                         ->where('tk.isactive', '=', 1);
                })
                ->where('kb.companycode', $user->companycode)
                ->where('kb.ordernumber', $ordernumber) // Filter by specific Order Number only
                ->where('kb.status', 'PRINTED') // Only vehicles that have been printed
                ->whereNotNull('kb.solar')
                ->select([
                    'kb.plot',
                    'kb.nokendaraan',
                    'kb.jammulai',
                    'kb.jamselesai',
                    'kb.hourmeterstart',
                    'kb.hourmeterend',
                    'kb.solar',
                    'kb.ordernumber',
                    'kb.gudangconfirm',
                    'k.jenis',
                    'tk.nama as operator_nama'
                ])
                ->orderBy('kb.nokendaraan')
                ->get();
                
            if ($vehicleData->isEmpty()) {
                return redirect()->route('input.gudang-bbm.index')
                    ->with('error', 'Tidak ada data kendaraan yang sudah diprint');
            }
            
            // Calculate totals
            $totalSolar = $vehicleData->sum('solar');
            $totalHourMeter = $vehicleData->sum(function($item) {
                return ($item->hourmeterend ?? 0) - ($item->hourmeterstart ?? 0);
            });
            
            // Get order number from existing data
            $orderNumber = $ordernumber; // Use the parameter directly
            $printDate = now()->format('d F Y, H:i:s');
            
            // Check if can be confirmed (belum dikonfirmasi gudang)
            $canConfirm = $vehicleData->where('gudangconfirm', 0)->count() > 0;
            
            return view('input.gudang.gudang-bbm-show', compact(
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
            
            return redirect()->route('input.gudang-bbm.index')
                ->with('error', 'Terjadi kesalahan saat memuat halaman: ' . $e->getMessage());
        }
    }

    /**
     * Mark BBM order as confirmed (by Order Number)
     */
    public function markConfirmed(Request $request, $ordernumber)
    {
        try {
            $request->validate([
                'ordernumber' => 'required|string'
            ]);
            
            $user = auth()->user();
            
            // Verify Order exists and belongs to this company
            $orderExists = DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
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
            
            // Update all vehicle records for this Order to confirmed
            $updated = DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('ordernumber', $ordernumber)
                ->where('status', 'PRINTED') // Only update printed records
                ->where('gudangconfirm', 0) // Only update unconfirmed records
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
     */
    private function getConfirmedTodayCount($companycode, $filterDate)
    {
        $query = DB::table('kendaraanbbm as kb')
            ->join('lkhhdr as lkh', function($join) {
                $join->on('kb.companycode', '=', 'lkh.companycode')
                     ->on('kb.lkhno', '=', 'lkh.lkhno');
            })
            ->where('kb.companycode', $companycode)
            ->where('kb.status', 'PRINTED')
            ->where('kb.gudangconfirm', 1);
            
        if ($filterDate) {
            $query->whereDate('lkh.lkhdate', $filterDate);
        }
        
        return $query->distinct('kb.lkhno', 'kb.nokendaraan')->count();
    }
}