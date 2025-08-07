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
     * Display a listing of BBM orders ready for printing
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $search = $request->input('search');
            $filterDate = $request->input('filter_date', now()->format('Y-m-d'));
            
            // Build query for BBM orders ready to print
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
                ->where('lkh.mobile_status', 'COMPLETED') // Only show completed LKH
                ->where('kb.status', 'INPUTTED') // Only show vehicles that have been inputted by admin kendaraan
                ->whereNotNull('kb.solar') // Must have solar data
                ->select([
                    'kb.lkhno',
                    'kb.plot',
                    'kb.nokendaraan', 
                    'kb.jammulai',
                    'kb.jamselesai',
                    'kb.hourmeterstart',
                    'kb.hourmeterend',
                    'kb.solar',
                    'kb.status',
                    'kb.createdat',
                    'lkh.lkhdate as lkh_date',
                    'lkh.activitycode',
                    'act.activityname',
                    'k.jenis',
                    'tk.nama as operator_nama'
                ]);
            
            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('kb.lkhno', 'like', "%{$search}%")
                      ->orWhere('kb.nokendaraan', 'like', "%{$search}%")
                      ->orWhere('tk.nama', 'like', "%{$search}%");
                });
            }
            
            // Apply date filter
            if ($filterDate) {
                $query->whereDate('lkh.lkhdate', $filterDate);
            }
            
            // Group by LKH and Vehicle to handle multiple plots
            $rawData = $query->orderBy('lkh.lkhdate', 'desc')
                           ->orderBy('kb.lkhno')
                           ->orderBy('kb.nokendaraan')
                           ->get();
            
            // Group plots for same LKH-Vehicle combination
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
            
            // Paginate manually
            $page = $request->input('page', 1);
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            
            $bbmData = new \Illuminate\Pagination\LengthAwarePaginator(
                $groupedData->slice($offset, $perPage),
                $groupedData->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            // Calculate statistics
            $stats = [
                'ready_print' => $groupedData->where('status', 'INPUTTED')->count(),
                'printed' => $this->getPrintedCount($user->companycode, $filterDate),
                'total_solar' => $groupedData->sum('solar'),
                'total_hm' => $groupedData->sum(function($item) {
                    return ($item->hourmeterend ?? 0) - ($item->hourmeterstart ?? 0);
                })
            ];
            
            return view('input.gudang.gudang-bbm', compact(
                'bbmData',
                'stats', 
                'search',
                'filterDate'
            ))->with([
                'title' => 'Gudang BBM - Order BBM',
                'navbar' => 'Input Gudang BBM',
                'nav' => 'BBM'
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
     * Show print view for specific LKH
     */
    public function print($lkhno)
    {
        try {
            $user = auth()->user();
            
            // Get LKH header data
            $lkhData = DB::table('lkhhdr as lkh')
                ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
                ->leftJoin('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->where('lkh.companycode', $user->companycode)
                ->where('lkh.lkhno', $lkhno)
                ->where('lkh.mobile_status', 'COMPLETED')
                ->select([
                    'lkh.*',
                    'u.name as mandor_nama',
                    'act.activityname'
                ])
                ->first();
                
            if (!$lkhData) {
                return redirect()->route('input.gudang.bbm.index')
                    ->with('error', 'Data LKH tidak ditemukan atau belum completed');
            }
            
            // Get vehicle data for this LKH
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
                ->where('kb.lkhno', $lkhno)
                ->where('kb.status', 'INPUTTED') // Only vehicles that have been processed by admin kendaraan
                ->whereNotNull('kb.solar')
                ->select([
                    'kb.plot',
                    'kb.nokendaraan',
                    'kb.jammulai',
                    'kb.jamselesai',
                    'kb.hourmeterstart',
                    'kb.hourmeterend',
                    'kb.solar',
                    'k.jenis',
                    'tk.nama as operator_nama'
                ])
                ->orderBy('kb.nokendaraan')
                ->get();
                
            if ($vehicleData->isEmpty()) {
                return redirect()->route('input.gudang.bbm.index')
                    ->with('error', 'Tidak ada data kendaraan yang siap untuk diprint');
            }
            
            // Calculate totals
            $totalSolar = $vehicleData->sum('solar');
            $totalHourMeter = $vehicleData->sum(function($item) {
                return ($item->hourmeterend ?? 0) - ($item->hourmeterstart ?? 0);
            });
            
            // Generate order number and print date
            $orderNumber = $this->generateOrderNumber($lkhno);
            $printDate = now()->format('d F Y, H:i:s');
            
            // Check if can mark as printed (status still INPUTTED)
            $canMarkPrinted = DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $lkhno)
                ->where('status', 'INPUTTED')
                ->exists();
            
            return view('input.gudang.show', compact(
                'lkhData',
                'vehicleData',
                'totalSolar',
                'totalHourMeter',
                'orderNumber',
                'printDate',
                'canMarkPrinted'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error in GudangBbmController@print', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'lkhno' => $lkhno
            ]);
            
            return redirect()->route('input.gudang.bbm.index')
                ->with('error', 'Terjadi kesalahan saat memuat halaman print: ' . $e->getMessage());
        }
    }

    /**
     * Mark BBM order as printed
     */
    public function markPrinted(Request $request, $lkhno)
    {
        try {
            $request->validate([
                'lkhno' => 'required|string'
            ]);
            
            $user = auth()->user();
            
            // Verify LKH exists and belongs to this company
            $lkhExists = DB::table('lkhhdr')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $lkhno)
                ->where('mobile_status', 'COMPLETED')
                ->exists();
                
            if (!$lkhExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'LKH tidak ditemukan atau belum completed'
                ]);
            }
            
            DB::beginTransaction();
            
            // Update all vehicle records for this LKH to PRINTED status
            $updated = DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $lkhno)
                ->where('status', 'INPUTTED') // Only update records that are ready for print
                ->update([
                    'status' => 'PRINTED',
                    'printedby' => $user->name,
                    'printedat' => now(),
                    'adminupdateby' => $user->name,
                    'adminupdatedat' => now()
                ]);
            
            if ($updated === 0) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang bisa diupdate atau sudah diprint sebelumnya'
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menandai {$updated} kendaraan sebagai sudah diprint"
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error in GudangBbmController@markPrinted', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'lkhno' => $lkhno
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menandai sebagai printed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate order number for BBM
     */
    private function generateOrderNumber($lkhno)
    {
        $date = now();
        $dateStr = $date->format('dmy');
        
        // Format: BBM-DDMMYY-LKHNO
        return "BBM-{$dateStr}-{$lkhno}";
    }

    /**
     * Get count of printed orders for statistics
     */
    private function getPrintedCount($companycode, $filterDate)
    {
        $query = DB::table('kendaraanbbm as kb')
            ->join('lkhhdr as lkh', function($join) {
                $join->on('kb.companycode', '=', 'lkh.companycode')
                     ->on('kb.lkhno', '=', 'lkh.lkhno');
            })
            ->where('kb.companycode', $companycode)
            ->where('kb.status', 'PRINTED');
            
        if ($filterDate) {
            $query->whereDate('lkh.lkhdate', $filterDate);
        }
        
        return $query->distinct('kb.lkhno', 'kb.nokendaraan')->count();
    }
}