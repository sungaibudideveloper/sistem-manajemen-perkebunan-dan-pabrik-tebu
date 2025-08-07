<?php

namespace App\Http\Controllers\Input;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class KendaraanController extends Controller
{
    /**
     * Display a listing of vehicle BBM data
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $search = $request->input('search');
            $filterDate = $request->input('filter_date', now()->format('Y-m-d'));
            
            // Build query
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
                    'kb.ordernumber',
                    'kb.printedby',
                    'kb.printedat',
                    'kb.createdat',
                    'lkh.lkhdate as lkh_date',
                    'lkh.activitycode',
                    'act.activityname',
                    'k.jenis',
                    'tk.nama as operator_nama',
                    DB::raw('TIMESTAMPDIFF(HOUR, CONCAT(DATE(lkh.lkhdate), " ", kb.jammulai), CONCAT(DATE(lkh.lkhdate), " ", kb.jamselesai)) as work_duration')
                ]);
            
            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('kb.lkhno', 'like', "%{$search}%")
                      ->orWhere('kb.nokendaraan', 'like', "%{$search}%")
                      ->orWhere('kb.ordernumber', 'like', "%{$search}%")
                      ->orWhere('tk.nama', 'like', "%{$search}%")
                      ->orWhere('act.activityname', 'like', "%{$search}%");
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
            
            $kendaraanData = new \Illuminate\Pagination\LengthAwarePaginator(
                $groupedData->slice($offset, $perPage),
                $groupedData->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            // Calculate statistics
            $stats = [
                'total' => $groupedData->count(),
                'pending' => $groupedData->where('status', null)->count(),
                'completed' => $groupedData->where('status', 'INPUTTED')->count(),
                'printed' => $groupedData->where('status', 'PRINTED')->count(),
            ];
            
            return view('input.kendaraan.index', compact(
                'kendaraanData',
                'stats', 
                'search',
                'filterDate'
            ))->with([
                'title' => 'Input Data Kendaraan',
                'navbar' => 'Input Data Kendaraan',
                'nav' => 'Kendaraan'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in KendaraanController@index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Store vehicle BBM data
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'lkhno' => 'required|string',
                'plot' => 'required|string',
                'nokendaraan' => 'required|string',
                'hourmeterstart' => 'required|numeric|min:0',
                'hourmeterend' => 'required|numeric|gt:hourmeterstart',
                'solar' => 'required|numeric|min:0.01'
            ]);
            
            $user = auth()->user();
            
            // Verify the record exists and belongs to this company
            $existingRecord = DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $request->lkhno)
                ->where('plot', $request->plot)
                ->where('nokendaraan', $request->nokendaraan)
                ->first();
            
            if (!$existingRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kendaraan tidak ditemukan'
                ]);
            }
            
            if ($existingRecord->status === 'PRINTED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah diprint dan tidak dapat diubah'
                ]);
            }
            
            DB::beginTransaction();
            
            // Update the BBM data
            DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $request->lkhno)
                ->where('plot', $request->plot)
                ->where('nokendaraan', $request->nokendaraan)
                ->update([
                    'hourmeterstart' => $request->hourmeterstart,
                    'hourmeterend' => $request->hourmeterend,
                    'solar' => $request->solar,
                    'status' => 'INPUTTED',
                    'adminupdateby' => $user->name,
                    'adminupdatedat' => now()
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Data kendaraan berhasil disimpan'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error in KendaraanController@store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update vehicle BBM data
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'lkhno' => 'required|string',
                'plot' => 'required|string',
                'nokendaraan' => 'required|string',
                'hourmeterstart' => 'required|numeric|min:0',
                'hourmeterend' => 'required|numeric|gt:hourmeterstart',
                'solar' => 'required|numeric|min:0.01'
            ]);
            
            $user = auth()->user();
            
            // Verify the record exists and belongs to this company
            $existingRecord = DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $request->lkhno)
                ->where('plot', $request->plot)
                ->where('nokendaraan', $request->nokendaraan)
                ->first();
            
            if (!$existingRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kendaraan tidak ditemukan'
                ]);
            }
            
            if ($existingRecord->status === 'PRINTED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah diprint dan tidak dapat diubah'
                ]);
            }
            
            DB::beginTransaction();
            
            // Update the BBM data
            DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $request->lkhno)
                ->where('plot', $request->plot)
                ->where('nokendaraan', $request->nokendaraan)
                ->update([
                    'hourmeterstart' => $request->hourmeterstart,
                    'hourmeterend' => $request->hourmeterend,
                    'solar' => $request->solar,
                    'status' => 'INPUTTED',
                    'adminupdateby' => $user->name,
                    'adminupdatedat' => now()
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Data kendaraan berhasil diupdate'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error in KendaraanController@update', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mark as printed dan generate order number - dipanggil dari tombol print
     */
    public function markPrinted($lkhno)
    {
        try {
            $user = auth()->user();
            
            // Verify data exists and is ready to be printed
            $existingData = DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $lkhno)
                ->where('status', 'INPUTTED')
                ->whereNotNull('solar')
                ->get();
                
            if ($existingData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang siap untuk diprint atau sudah diprint sebelumnya'
                ]);
            }
            
            DB::beginTransaction();
            
            // Generate order number
            $orderNumber = $this->generateOrderNumber();
            
            // Update all vehicle records for this LKH to PRINTED status
            $updated = DB::table('kendaraanbbm')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $lkhno)
                ->where('status', 'INPUTTED')
                ->update([
                    'status' => 'PRINTED',
                    'ordernumber' => $orderNumber,
                    'printedby' => $user->name,
                    'printedat' => now(),
                    'adminupdateby' => $user->name,
                    'adminupdatedat' => now()
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Order #{$orderNumber} berhasil di-generate untuk {$updated} kendaraan",
                'order_number' => $orderNumber
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error in KendaraanController@markPrinted', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'lkhno' => $lkhno
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses order: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display print page
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
                return redirect()->route('input.kendaraan.index')
                    ->with('error', 'Data LKH tidak ditemukan atau belum completed');
            }
            
            // Get vehicle data for this LKH (both INPUTTED and PRINTED status)
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
                ->whereIn('kb.status', ['INPUTTED', 'PRINTED']) // Allow both status
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
                    'k.jenis',
                    'tk.nama as operator_nama'
                ])
                ->orderBy('kb.nokendaraan')
                ->get();
                
            if ($vehicleData->isEmpty()) {
                return redirect()->route('input.kendaraan.index')
                    ->with('error', 'Tidak ada data kendaraan untuk diprint');
            }
            
            // Calculate totals
            $totalSolar = $vehicleData->sum('solar');
            $totalHourMeter = $vehicleData->sum(function($item) {
                return ($item->hourmeterend ?? 0) - ($item->hourmeterstart ?? 0);
            });
            
            // Get order number - hanya dari data yang sudah tersimpan, jangan generate baru
            $orderNumber = request('ordernumber') 
                ?? $vehicleData->sortByDesc('printedat')->first()->ordernumber 
                ?? 'DRAFT-' . now()->format('YmdHis');
            $printDate = now()->format('d F Y, H:i:s');
            
            return view('input.kendaraan.show', compact(
                'lkhData',
                'vehicleData',
                'totalSolar',
                'totalHourMeter',
                'orderNumber',
                'printDate'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error in KendaraanController@print', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'lkhno' => $lkhno
            ]);
            
            return redirect()->route('input.kendaraan.index')
                ->with('error', 'Terjadi kesalahan saat memuat halaman print: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique order number (6 digit, start from 000433)
     */
    private function generateOrderNumber()
    {
        $user = auth()->user();
        $companycode = $user->companycode;
        
        try {
            // Get last order number from database
            $lastOrder = DB::table('kendaraanbbm')
                ->where('companycode', $companycode)
                ->whereNotNull('ordernumber')
                ->orderByDesc('ordernumber')
                ->first();
                
            if ($lastOrder && $lastOrder->ordernumber) {
                // Increment dari order number terakhir
                $lastNumber = (int) $lastOrder->ordernumber;
                $nextNumber = $lastNumber + 1;
            } else {
                // Start dari 433 jika belum ada order number
                $nextNumber = 433;
            }
            
            // Format jadi 6 digit dengan leading zeros
            return str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            
        } catch (\Exception $e) {
            Log::error('Error generating order number: ' . $e->getMessage());
            // Fallback ke timestamp-based number jika error
            return str_pad(time() % 999999, 6, '0', STR_PAD_LEFT);
        }
    }
}