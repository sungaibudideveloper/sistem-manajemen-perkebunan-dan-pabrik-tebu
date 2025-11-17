<?php

namespace App\Http\Controllers\Input;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * KendaraanController - Updated untuk lkhdetailkendaraan (NO PLOT!)
 * 
 * Manages vehicle BBM (fuel) data for workshop operations
 * - Admin workshop input hourmeter + solar
 * - Print BBM order with order number
 * - Gudang BBM confirmation
 */
class KendaraanController extends Controller
{
    /**
     * Display a listing of vehicle BBM data
     * ✅ FIXED: Get plots via JOIN to lkhdetailplot
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $search = $request->input('search');
            $filterDate = $request->input('filter_date', now()->format('Y-m-d'));
            
            // ✅ FIXED: Get plots via JOIN
            $query = DB::table('lkhdetailkendaraan as lk')
                ->join('lkhhdr as lh', function($join) {
                    $join->on('lk.companycode', '=', 'lh.companycode')
                         ->on('lk.lkhno', '=', 'lh.lkhno');
                })
                ->join('kendaraan as k', function($join) use ($user) {
                    $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                         ->where('k.companycode', '=', $user->companycode)
                         ->where('k.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk_operator', function($join) use ($user) {
                    $join->on('lk.operatorid', '=', 'tk_operator.tenagakerjaid')
                         ->where('tk_operator.companycode', '=', $user->companycode)
                         ->where('tk_operator.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk_helper', function($join) use ($user) {
                    $join->on('lk.helperid', '=', 'tk_helper.tenagakerjaid')
                         ->where('tk_helper.companycode', '=', $user->companycode)
                         ->where('tk_helper.isactive', '=', 1);
                })
                ->leftJoin('activity as act', 'lh.activitycode', '=', 'act.activitycode')
                // ✅ NEW: JOIN to get plots
                ->leftJoin('lkhdetailplot as ldp', function($join) {
                    $join->on('lk.companycode', '=', 'ldp.companycode')
                         ->on('lk.lkhno', '=', 'ldp.lkhno');
                })
                ->where('lk.companycode', $user->companycode)
                ->whereNotNull('lk.jammulai')
                ->whereNotNull('lk.jamselesai')
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
                    // ✅ Get plots via GROUP_CONCAT
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
            
            // Apply date filter
            if ($filterDate) {
                $query->whereDate('lh.lkhdate', $filterDate);
            }
            
            // ✅ Group by kendaraan (not plot!)
            $query->groupBy([
                'lk.id', 'lk.lkhno', 'lk.nokendaraan',
                'lk.jammulai', 'lk.jamselesai', 'lk.hourmeterstart',
                'lk.hourmeterend', 'lk.solar', 'lk.status',
                'lk.ordernumber', 'lk.printedby', 'lk.printedat',
                'lk.createdat', 'lh.lkhdate', 'lh.activitycode',
                'lh.mandorid', 'act.activityname', 'k.jenis',
                'tk_operator.nama', 'tk_helper.nama'
            ]);
            
            $kendaraanData = $query->orderBy('lh.lkhdate', 'desc')
                                  ->orderBy('lk.lkhno')
                                  ->orderBy('lk.nokendaraan')
                                  ->paginate(20);
            
            // Calculate statistics
            $stats = [
                'total' => DB::table('lkhdetailkendaraan')
                    ->where('companycode', $user->companycode)
                    ->whereNotNull('jammulai')
                    ->whereDate('createdat', '>=', now()->subDays(7))
                    ->count(),
                'pending' => DB::table('lkhdetailkendaraan')
                    ->where('companycode', $user->companycode)
                    ->whereNotNull('jammulai')
                    ->whereNull('status')
                    ->count(),
                'completed' => DB::table('lkhdetailkendaraan')
                    ->where('companycode', $user->companycode)
                    ->where('status', 'INPUTTED')
                    ->count(),
                'printed' => DB::table('lkhdetailkendaraan')
                    ->where('companycode', $user->companycode)
                    ->where('status', 'PRINTED')
                    ->count(),
            ];
            
            return view('input.kendaraan-workshop.index', compact(
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
     * ✅ FIXED: No plot field
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
            
            $user = auth()->user();
            
            $existingRecord = DB::table('lkhdetailkendaraan')
                ->where('id', $request->id)
                ->where('companycode', $user->companycode)
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
                ->where('companycode', $user->companycode)
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
     * ✅ FIXED: No plot field
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
            
            $user = auth()->user();
            
            $existingRecord = DB::table('lkhdetailkendaraan')
                ->where('id', $request->id)
                ->where('companycode', $user->companycode)
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
                ->where('companycode', $user->companycode)
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
     * ✅ FIXED: No plot field
     */
    public function markPrinted($lkhno)
    {
        try {
            $user = auth()->user();
            
            $existingData = DB::table('lkhdetailkendaraan')
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
            
            $orderNumber = $this->generateOrderNumber();
            
            $updated = DB::table('lkhdetailkendaraan')
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
     * ✅ FIXED: Get plots via JOIN
     */
    public function print($lkhno)
    {
        try {
            $user = auth()->user();
            
            $lkhData = DB::table('lkhhdr as lkh')
                ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
                ->leftJoin('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->where('lkh.companycode', $user->companycode)
                ->where('lkh.lkhno', $lkhno)
                ->select([
                    'lkh.*',
                    'u.name as mandor_nama',
                    'act.activityname'
                ])
                ->first();
                
            if (!$lkhData) {
                return redirect()->route('input.kendaraan-workshop.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }
            
            // ✅ FIXED: Get plots via JOIN
            $vehicleData = DB::table('lkhdetailkendaraan as lk')
                ->join('kendaraan as k', function($join) use ($user) {
                    $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                        ->where('k.companycode', '=', $user->companycode)
                        ->where('k.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk_operator', function($join) use ($user) {
                    $join->on('lk.operatorid', '=', 'tk_operator.tenagakerjaid')
                        ->where('tk_operator.companycode', '=', $user->companycode)
                        ->where('tk_operator.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk_helper', function($join) use ($user) {
                    $join->on('lk.helperid', '=', 'tk_helper.tenagakerjaid')
                        ->where('tk_helper.companycode', '=', $user->companycode)
                        ->where('tk_helper.isactive', '=', 1);
                })
                // ✅ NEW: JOIN to get plots
                ->leftJoin('lkhdetailplot as ldp', function($join) use ($user) {
                    $join->on('lk.companycode', '=', 'ldp.companycode')
                         ->on('lk.lkhno', '=', 'ldp.lkhno');
                })
                ->where('lk.companycode', $user->companycode)
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
                    // ✅ Get plots via GROUP_CONCAT
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
                return redirect()->route('input.kendaraan-workshop.index')
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
            
            return view('input.kendaraan-workshop.show', compact(
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
            
            return redirect()->route('input.kendaraan-workshop.index')
                ->with('error', 'Terjadi kesalahan saat memuat halaman print: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        $user = auth()->user();
        $companycode = $user->companycode;
        
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
     * ✅ Get kendaraan details for specific LKH (API endpoint)
     * ✅ FIXED: Include plots via JOIN
     */
    public function getKendaraanForLkh($lkhno)
    {
        try {
            $user = auth()->user();
            
            $kendaraanData = DB::table('lkhdetailkendaraan as lk')
                ->join('kendaraan as k', function($join) use ($user) {
                    $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                        ->where('k.companycode', '=', $user->companycode);
                })
                ->leftJoin('tenagakerja as tk_operator', function($join) use ($user) {
                    $join->on('lk.operatorid', '=', 'tk_operator.tenagakerjaid')
                        ->where('tk_operator.companycode', '=', $user->companycode);
                })
                ->leftJoin('tenagakerja as tk_helper', function($join) use ($user) {
                    $join->on('lk.helperid', '=', 'tk_helper.tenagakerjaid')
                        ->where('tk_helper.companycode', '=', $user->companycode);
                })
                // ✅ NEW: JOIN to get plots
                ->leftJoin('lkhdetailplot as ldp', function($join) {
                    $join->on('lk.companycode', '=', 'ldp.companycode')
                         ->on('lk.lkhno', '=', 'ldp.lkhno');
                })
                ->where('lk.companycode', $user->companycode)
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
     * ✅ FIXED: No plot field
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
            
            $user = auth()->user();
            $companycode = $user->companycode;
            
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