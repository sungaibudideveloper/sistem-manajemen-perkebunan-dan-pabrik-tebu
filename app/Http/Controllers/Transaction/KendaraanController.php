<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

/**
 * KendaraanController - Fixed: Use Session company instead of User company
 */
class KendaraanController extends Controller
{
    /**
     * Display a listing of vehicle BBM data
     *  FIXED: Use Session::get('companycode') instead of auth()->user()->companycode
     */
    public function index(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $user = auth()->user();
            
            $search = $request->input('search');
            
            // ✅ FIX: Sama seperti Gudang BBM
            $filterDate = $request->input('filter_date', now()->format('Y-m-d')); // Default = today
            $showAllDate = $request->boolean('show_all_date'); // ❌ HAPUS parameter kedua (default = false)
            
            $query = DB::table('lkhdetailkendaraan as lk')
                ->join('lkhhdr as lh', function($join) use ($companycode) {
                    $join->on('lk.companycode', '=', 'lh.companycode')
                        ->on('lk.lkhno', '=', 'lh.lkhno')
                        ->where('lh.companycode', '=', $companycode);
                })
                ->join('kendaraan as k', function($join) use ($companycode) {
                    $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                        ->where('k.companycode', '=', $companycode)
                        ->where('k.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk_operator', function($join) use ($companycode) {
                    $join->on('lk.operatorid', '=', 'tk_operator.tenagakerjaid')
                        ->where('tk_operator.companycode', '=', $companycode)
                        ->where('tk_operator.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk_helper', function($join) use ($companycode) {
                    $join->on('lk.helperid', '=', 'tk_helper.tenagakerjaid')
                        ->where('tk_helper.companycode', '=', $companycode)
                        ->where('tk_helper.isactive', '=', 1);
                })
                ->leftJoin('activity as act', 'lh.activitycode', '=', 'act.activitycode')
                ->leftJoin('lkhdetailplot as ldp', function($join) use ($companycode) {
                    $join->on('lk.companycode', '=', 'ldp.companycode')
                        ->on('lk.lkhno', '=', 'ldp.lkhno')
                        ->where('ldp.companycode', '=', $companycode);
                })
                ->where('lk.companycode', $companycode)
                ->whereNotNull('lk.jammulai')
                ->whereNotNull('lk.jamselesai')
                ->select([
                    'lk.id',
                    'lk.companycode',
                    'lk.lkhno',
                    'lk.nokendaraan', 
                    'lk.jammulai',
                    'lk.jamselesai',
                    'lk.hourmeterstart',
                    'lk.hourmeterend',
                    'lk.solar',
                    'lk.status',
                    'lk.ordernumber',
                    'lk.printedby',
                    'lk.printedat',
                    'lk.createdat',
                    'lh.lkhdate as lkh_date',
                    'lh.activitycode',
                    'lh.mandorid',
                    'act.activityname',
                    'k.jenis',
                    'tk_operator.nama as operator_nama',
                    'tk_helper.nama as helper_nama',
                    DB::raw('GROUP_CONCAT(DISTINCT ldp.plot ORDER BY ldp.plot SEPARATOR ", ") as plots'),
                    DB::raw('TIMESTAMPDIFF(HOUR, lk.jammulai, lk.jamselesai) as work_duration')
                ]);
            
            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('lk.lkhno', 'like', "%{$search}%")
                    ->orWhere('lk.nokendaraan', 'like', "%{$search}%")
                    ->orWhere('lk.ordernumber', 'like', "%{$search}%")
                    ->orWhere('tk_operator.nama', 'like', "%{$search}%")
                    ->orWhere('act.activityname', 'like', "%{$search}%");
                });
            }
            
            // ✅ FIX: Apply date filter - SAMA SEPERTI GUDANG BBM
            if ($filterDate && !$showAllDate) {
                $query->whereDate('lh.lkhdate', $filterDate);
            }
            
            // Group by kendaraan
            $query->groupBy([
                'lk.id', 
                'lk.companycode',
                'lk.lkhno', 
                'lk.nokendaraan',
                'lk.jammulai', 
                'lk.jamselesai', 
                'lk.hourmeterstart',
                'lk.hourmeterend', 
                'lk.solar', 
                'lk.status',
                'lk.ordernumber', 
                'lk.printedby', 
                'lk.printedat',
                'lk.createdat', 
                'lh.lkhdate', 
                'lh.activitycode',
                'lh.mandorid', 
                'act.activityname', 
                'k.jenis',
                'tk_operator.nama', 
                'tk_helper.nama'
            ]);
            
            $kendaraanData = $query->orderBy('lh.lkhdate', 'desc')
                                ->orderBy('lk.lkhno')
                                ->orderBy('lk.nokendaraan')
                                ->paginate(20);
            
            // Calculate statistics
            $statsQuery = DB::table('lkhdetailkendaraan as lk')
                ->join('lkhhdr as lh', function($join) use ($companycode) {
                    $join->on('lk.companycode', '=', 'lh.companycode')
                        ->on('lk.lkhno', '=', 'lh.lkhno')
                        ->where('lh.companycode', '=', $companycode);
                })
                ->where('lk.companycode', $companycode)
                ->whereNotNull('lk.jammulai');
            
            // ✅ FIX: Apply same date filter to stats
            if ($filterDate && !$showAllDate) {
                $statsQuery->whereDate('lh.lkhdate', $filterDate);
            }
            
            $stats = [
                'total' => (clone $statsQuery)->count(),
                'pending' => (clone $statsQuery)->whereNull('lk.status')->count(),
                'completed' => (clone $statsQuery)->where('lk.status', 'INPUTTED')->count(),
                'printed' => (clone $statsQuery)->where('lk.status', 'PRINTED')->count(),
            ];
            
            return view('transaction.kendaraan-workshop.index', compact(
                'kendaraanData',
                'stats', 
                'search',
                'filterDate',
                'showAllDate'
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
                'id' => 'required|integer',
                'hourmeterstart' => 'required|numeric|min:0',
                'hourmeterend' => 'required|numeric|gt:hourmeterstart',
                'solar' => 'required|numeric|min:0.01'
            ]);
            
            $companycode = Session::get('companycode');
            $user = auth()->user();
            
            $existingRecord = DB::table('lkhdetailkendaraan')
                ->where('id', $request->id)
                ->where('companycode', $companycode)
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
            
            DB::table('lkhdetailkendaraan')
                ->where('id', $request->id)
                ->where('companycode', $companycode)
                ->update([
                    'hourmeterstart' => $request->hourmeterstart,
                    'hourmeterend' => $request->hourmeterend,
                    'solar' => $request->solar,
                    'status' => 'INPUTTED',
                    'adminupdateby' => $user->name,
                    'adminupdatedat' => now()
                ]);
            
            DB::commit();
            
            Log::info('Kendaraan BBM data stored', [
                'id' => $request->id,
                'lkhno' => $existingRecord->lkhno,
                'nokendaraan' => $existingRecord->nokendaraan,
                'solar' => $request->solar,
                'user' => $user->name
            ]);
            
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
                'id' => 'required|integer',
                'hourmeterstart' => 'required|numeric|min:0',
                'hourmeterend' => 'required|numeric|gt:hourmeterstart',
                'solar' => 'required|numeric|min:0.01'
            ]);
            
            $companycode = Session::get('companycode');
            $user = auth()->user();
            
            $existingRecord = DB::table('lkhdetailkendaraan')
                ->where('id', $request->id)
                ->where('companycode', $companycode)
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
            
            DB::table('lkhdetailkendaraan')
                ->where('id', $request->id)
                ->where('companycode', $companycode)
                ->update([
                    'hourmeterstart' => $request->hourmeterstart,
                    'hourmeterend' => $request->hourmeterend,
                    'solar' => $request->solar,
                    'status' => 'INPUTTED',
                    'adminupdateby' => $user->name,
                    'adminupdatedat' => now()
                ]);
            
            DB::commit();
            
            Log::info('Kendaraan BBM data updated', [
                'id' => $request->id,
                'lkhno' => $existingRecord->lkhno,
                'nokendaraan' => $existingRecord->nokendaraan,
                'user' => $user->name
            ]);
            
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
     * Mark as printed dan generate order number
     */
    public function markPrinted($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $user = auth()->user();
            
            $existingData = DB::table('lkhdetailkendaraan')
                ->where('companycode', $companycode)
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
            
            $orderNumber = $this->generateOrderNumber();
            
            $updated = DB::table('lkhdetailkendaraan')
                ->where('companycode', $companycode)
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
            
            Log::info('BBM order printed', [
                'lkhno' => $lkhno,
                'order_number' => $orderNumber,
                'total_vehicles' => $updated,
                'user' => $user->name
            ]);
            
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
            $companycode = Session::get('companycode');
            $user = auth()->user();
            
            $lkhData = DB::table('lkhhdr as lkh')
                ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
                ->leftJoin('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->where('lkh.companycode', $companycode)
                ->where('lkh.lkhno', $lkhno)
                ->select([
                    'lkh.*',
                    'u.name as mandor_nama',
                    'act.activityname'
                ])
                ->first();
                
            if (!$lkhData) {
                return redirect()->route('transaction.kendaraan-workshop.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }
            
            $vehicleData = DB::table('lkhdetailkendaraan as lk')
                ->join('kendaraan as k', function($join) use ($companycode) {
                    $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                        ->where('k.companycode', '=', $companycode)
                        ->where('k.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk_operator', function($join) use ($companycode) {
                    $join->on('lk.operatorid', '=', 'tk_operator.tenagakerjaid')
                        ->where('tk_operator.companycode', '=', $companycode)
                        ->where('tk_operator.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk_helper', function($join) use ($companycode) {
                    $join->on('lk.helperid', '=', 'tk_helper.tenagakerjaid')
                        ->where('tk_helper.companycode', '=', $companycode)
                        ->where('tk_helper.isactive', '=', 1);
                })
                ->leftJoin('lkhdetailplot as ldp', function($join) use ($companycode) {
                    $join->on('lk.companycode', '=', 'ldp.companycode')
                         ->on('lk.lkhno', '=', 'ldp.lkhno')
                         ->where('ldp.companycode', '=', $companycode);
                })
                ->where('lk.companycode', $companycode)
                ->where('lk.lkhno', $lkhno)
                ->whereIn('lk.status', ['INPUTTED', 'PRINTED'])
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
                    'k.jenis',
                    'tk_operator.nama as operator_nama',
                    'tk_helper.nama as helper_nama',
                    DB::raw('GROUP_CONCAT(DISTINCT ldp.plot ORDER BY ldp.plot SEPARATOR ", ") as plots')
                ])
                ->groupBy([
                    'lk.id', 'lk.nokendaraan', 'lk.jammulai', 'lk.jamselesai',
                    'lk.hourmeterstart', 'lk.hourmeterend', 'lk.solar',
                    'lk.ordernumber', 'k.jenis', 'tk_operator.nama', 'tk_helper.nama'
                ])
                ->orderBy('lk.nokendaraan')
                ->get();
                
            if ($vehicleData->isEmpty()) {
                return redirect()->route('transaction.kendaraan-workshop.index')
                    ->with('error', 'Tidak ada data kendaraan untuk diprint');
            }
            
            $totalSolar = $vehicleData->sum('solar');
            $totalHourMeter = $vehicleData->sum(function($item) {
                return ($item->hourmeterend ?? 0) - ($item->hourmeterstart ?? 0);
            });
            
            $orderNumber = request('ordernumber') 
                ?? $vehicleData->sortByDesc('printedat')->first()->ordernumber 
                ?? 'DRAFT-' . now()->format('YmdHis');
            $printDate = now()->format('d F Y, H:i:s');
            
            Log::info('Print BBM order viewed', [
                'lkhno' => $lkhno,
                'order_number' => $orderNumber,
                'total_vehicles' => $vehicleData->count(),
                'total_solar' => $totalSolar,
                'user' => $user->name
            ]);
            
            return view('transaction.kendaraan-workshop.show', compact(
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
            
            return redirect()->route('transaction.kendaraan-workshop.index')
                ->with('error', 'Terjadi kesalahan saat memuat halaman print: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        $companycode = Session::get('companycode');
        
        try {
            $lastOrder = DB::table('lkhdetailkendaraan')
                ->where('companycode', $companycode)
                ->whereNotNull('ordernumber')
                ->orderByDesc('ordernumber')
                ->first();
                
            if ($lastOrder && $lastOrder->ordernumber) {
                $lastNumber = (int) $lastOrder->ordernumber;
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 433;
            }
            
            $orderNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            
            Log::info('Order number generated', [
                'company' => $companycode,
                'last_order' => $lastOrder ? $lastOrder->ordernumber : 'N/A',
                'new_order' => $orderNumber
            ]);
            
            return $orderNumber;
            
        } catch (\Exception $e) {
            Log::error('Error generating order number: ' . $e->getMessage());
            return str_pad(time() % 999999, 6, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Get kendaraan details for specific LKH (API endpoint)
     */
    public function getKendaraanForLkh($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $kendaraanData = DB::table('lkhdetailkendaraan as lk')
                ->join('kendaraan as k', function($join) use ($companycode) {
                    $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                        ->where('k.companycode', '=', $companycode);
                })
                ->leftJoin('tenagakerja as tk_operator', function($join) use ($companycode) {
                    $join->on('lk.operatorid', '=', 'tk_operator.tenagakerjaid')
                        ->where('tk_operator.companycode', '=', $companycode);
                })
                ->leftJoin('tenagakerja as tk_helper', function($join) use ($companycode) {
                    $join->on('lk.helperid', '=', 'tk_helper.tenagakerjaid')
                        ->where('tk_helper.companycode', '=', $companycode);
                })
                ->leftJoin('lkhdetailplot as ldp', function($join) {
                    $join->on('lk.companycode', '=', 'ldp.companycode')
                         ->on('lk.lkhno', '=', 'ldp.lkhno');
                })
                ->where('lk.companycode', $companycode)
                ->where('lk.lkhno', $lkhno)
                ->select([
                    'lk.id',
                    'lk.nokendaraan',
                    'k.jenis as vehicle_type',
                    'lk.operatorid',
                    'tk_operator.nama as operator_nama',
                    'tk_operator.nik as operator_nik',
                    'lk.helperid',
                    'tk_helper.nama as helper_nama',
                    'lk.jammulai',
                    'lk.jamselesai',
                    'lk.hourmeterstart',
                    'lk.hourmeterend',
                    'lk.solar',
                    'lk.status',
                    DB::raw('GROUP_CONCAT(DISTINCT ldp.plot ORDER BY ldp.plot SEPARATOR ", ") as plots')
                ])
                ->groupBy([
                    'lk.id', 'lk.nokendaraan', 'k.jenis', 'lk.operatorid',
                    'tk_operator.nama', 'tk_operator.nik', 'lk.helperid',
                    'tk_helper.nama', 'lk.jammulai', 'lk.jamselesai',
                    'lk.hourmeterstart', 'lk.hourmeterend', 'lk.solar', 'lk.status'
                ])
                ->orderBy('lk.nokendaraan')
                ->get();
            
            return response()->json([
                'success' => true,
                'lkhno' => $lkhno,
                'total_vehicles' => $kendaraanData->count(),
                'kendaraan' => $kendaraanData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in KendaraanController@getKendaraanForLkh', [
                'lkhno' => $lkhno,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data kendaraan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update kendaraan data (for handheld mobile)
     */
    public function bulkUpdateFromHandheld(Request $request)
    {
        try {
            $request->validate([
                'lkhno' => 'required|string',
                'vehicles' => 'required|array|min:1',
                'vehicles.*.id' => 'required|integer',
                'vehicles.*.jammulai' => 'required|date_format:H:i',
                'vehicles.*.jamselesai' => 'required|date_format:H:i|after:vehicles.*.jammulai'
            ]);
            
            $companycode = Session::get('companycode');
            $user = auth()->user();
            
            DB::beginTransaction();
            
            $updatedCount = 0;
            
            foreach ($request->vehicles as $vehicle) {
                $updated = DB::table('lkhdetailkendaraan')
                    ->where('id', $vehicle['id'])
                    ->where('companycode', $companycode)
                    ->where('lkhno', $request->lkhno)
                    ->update([
                        'jammulai' => $vehicle['jammulai'],
                        'jamselesai' => $vehicle['jamselesai'],
                        'adminupdateby' => $user->name,
                        'adminupdatedat' => now()
                    ]);
                
                if ($updated) {
                    $updatedCount++;
                }
            }
            
            DB::commit();
            
            Log::info('Kendaraan data bulk updated from handheld', [
                'lkhno' => $request->lkhno,
                'total_updated' => $updatedCount,
                'user' => $user->name
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil update {$updatedCount} kendaraan",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error in KendaraanController@bulkUpdateFromHandheld', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}