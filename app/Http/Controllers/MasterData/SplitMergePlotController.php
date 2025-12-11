<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * SplitMergePlotController
 * 
 * Handles plot split and merge REQUEST creation (not execution)
 * Execution triggered after approval via SplitMergePlotService
 */
class SplitMergePlotController extends Controller
{
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $perPage = (int) $request->input('perPage', 10);
        $search = $request->input('search');
        
        $query = DB::table('plottransaction as pt')
            ->leftJoin('approvaltransaction as at', function($join) use ($companycode) {
                $join->on('pt.transactionnumber', '=', 'at.transactionnumber')
                     ->where('at.companycode', '=', $companycode);
            })
            ->where('pt.companycode', $companycode)
            ->select([
                'pt.*',
                'at.approvalno',
                'at.approvalstatus',
                'at.approval1flag',
                'at.approval2flag',
                'at.approval3flag',
                'at.approval1idjabatan',
                'at.approval2idjabatan',
                'at.approval3idjabatan',
                DB::raw("DATE_FORMAT(pt.transactiondate, '%d/%m/%Y') as formatted_date"),
                DB::raw("DATE_FORMAT(pt.createdat, '%d/%m/%Y %H:%i') as formatted_createdat")
            ])
            ->orderBy('pt.createdat', 'desc');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('pt.dominantplot', 'like', "%{$search}%")
                  ->orWhere('pt.transactionnumber', 'like', "%{$search}%")
                  ->orWhere('pt.inputby', 'like', "%{$search}%");
            });
        }
        
        $transactions = $query->paginate($perPage);
        
        $transactions->getCollection()->transform(function($transaction) {
            $transaction->sourceplots_array = json_decode($transaction->sourceplots, true);
            $transaction->resultplots_array = json_decode($transaction->resultplots, true);
            $transaction->sourcebatches_array = json_decode($transaction->sourcebatches, true);
            $transaction->resultbatches_array = json_decode($transaction->resultbatches, true);
            $transaction->areamap_array = json_decode($transaction->areamap, true);
            return $transaction;
        });
        
        $activeBatches = $this->getActiveBatchesForSelection($companycode);
        
        return view('masterdata.split-merge-plot.index', [
            'title' => 'Rekonstruksi Plot',
            'navbar' => 'Master Data',
            'nav' => 'Rekonstruksi Plot',
            'transactions' => $transactions,
            'activeBatches' => $activeBatches,
            'perPage' => $perPage,
            'search' => $search,
        ]);
    }
    
    private function getActiveBatchesForSelection($companycode)
    {
        return DB::table('batch as b')
            ->join('masterlist as m', function($join) use ($companycode) {
                $join->on('b.batchno', '=', 'm.activebatchno')
                     ->where('m.companycode', '=', $companycode);
            })
            ->where('b.companycode', $companycode)
            ->where('b.isactive', 1)
            ->select(['b.batchno', 'b.plot', 'b.batcharea', 'b.lifecyclestatus', 'b.tanggalpanen', 'b.batchdate', 'm.blok'])
            ->orderBy('b.plot')
            ->get();
    }
    
    public function split(Request $request)
    {
        $request->validate([
            'source_batchno' => 'required|string|exists:batch,batchno',
            'splits' => 'required|array|min:2',
            'splits.*.plot' => 'required|string|max:5',
            'splits.*.area' => 'required|numeric|min:0.01',
            'dominant_plot' => 'required|string|max:5',
            'reason' => 'nullable|string|max:500'
        ]);
        
        $companycode = Session::get('companycode');
        $sourceBatchNo = $request->source_batchno;
        $splits = $request->splits;
        $dominantPlot = $request->dominant_plot;
        $reason = $request->reason;
        
        try {
            DB::beginTransaction();
            
            $sourceBatch = $this->getAndValidateBatch($companycode, $sourceBatchNo);
            
            $totalSplitArea = array_sum(array_column($splits, 'area'));
            if (abs($totalSplitArea - $sourceBatch->batcharea) > 0.01) {
                throw new \Exception("Total split area ({$totalSplitArea} Ha) must equal original batch area ({$sourceBatch->batcharea} Ha)");
            }
            
            $transactionNumber = $this->generateTransactionNumber($companycode, now());
            
            $resultPlots = array_column($splits, 'plot');
            $areaMap = [];
            foreach ($splits as $split) {
                $areaMap[$split['plot']] = $split['area'];
            }
            
            $resultBatches = [];
            foreach ($splits as $index => $split) {
                $resultBatches[] = $this->generateBatchNo($companycode, now(), $index);
            }
            
            DB::table('plottransaction')->insert([
                'transactionnumber' => $transactionNumber,
                'companycode' => $companycode,
                'transactiontype' => 'SPLIT',
                'transactiondate' => now()->format('Y-m-d'),
                'sourceplots' => json_encode([$sourceBatch->plot]),
                'resultplots' => json_encode($resultPlots),
                'sourcebatches' => json_encode([$sourceBatchNo]),
                'resultbatches' => json_encode($resultBatches),
                'areamap' => json_encode($areaMap),
                'dominantplot' => $dominantPlot,
                'splitmergedreason' => $reason,
                'inputby' => Auth::user()->userid,
                'createdat' => now()
            ]);
            
            $approvalMaster = DB::table('approval')
                ->where('companycode', $companycode)
                ->where('category', 'Approval Rekonstruksi Plot')
                ->first();
            
            if (!$approvalMaster) {
                throw new \Exception('Approval master untuk "Approval Rekonstruksi Plot" belum di-setup');
            }
            
            $approvalNo = $this->generateApprovalNo($companycode, now());
            
            DB::table('approvaltransaction')->insert([
                'approvalno' => $approvalNo,
                'companycode' => $companycode,
                'approvalcategoryid' => $approvalMaster->id,
                'transactionnumber' => $transactionNumber,
                'jumlahapproval' => $approvalMaster->jumlahapproval,
                'approval1idjabatan' => $approvalMaster->idjabatanapproval1,
                'approval2idjabatan' => $approvalMaster->idjabatanapproval2,
                'approval3idjabatan' => $approvalMaster->idjabatanapproval3,
                'approvalstatus' => null,
                'inputby' => Auth::user()->userid,
                'createdat' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Split request berhasil dibuat [{$transactionNumber}]. Menunggu approval.",
                'data' => [
                    'transaction_number' => $transactionNumber,
                    'approval_number' => $approvalNo,
                    'status' => 'PENDING_APPROVAL'
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Split request failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat split request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function merge(Request $request)
    {
        $request->validate([
            'source_batches' => 'required|array|min:2',
            'source_batches.*' => 'required|string|exists:batch,batchno',
            'result_plot' => 'required|string|max:5',
            'dominant_plot' => 'required|string|max:5',
            'reason' => 'nullable|string|max:500'
        ]);
        
        $companycode = Session::get('companycode');
        $sourceBatchNos = $request->source_batches;
        $resultPlot = $request->result_plot;
        $dominantPlot = $request->dominant_plot;
        $reason = $request->reason;
        
        try {
            DB::beginTransaction();
            
            $sourceBatches = [];
            $totalArea = 0;
            $sourcePlots = [];
            
            foreach ($sourceBatchNos as $batchNo) {
                $batch = $this->getAndValidateBatch($companycode, $batchNo);
                $sourceBatches[] = $batch;
                $totalArea += $batch->batcharea;
                $sourcePlots[] = $batch->plot;
            }
            
            $lifecycles = array_unique(array_column($sourceBatches, 'lifecyclestatus'));
            if (count($lifecycles) > 1) {
                throw new \Exception("Cannot merge batches with different lifecycle status");
            }
            
            $dominantBatch = collect($sourceBatches)->firstWhere('plot', $dominantPlot);
            if (!$dominantBatch) {
                throw new \Exception("Dominant plot tidak ditemukan");
            }
            
            $transactionNumber = $this->generateTransactionNumber($companycode, now());
            $newBatchNo = $this->generateBatchNo($companycode, now());
            
            DB::table('plottransaction')->insert([
                'transactionnumber' => $transactionNumber,
                'companycode' => $companycode,
                'transactiontype' => 'MERGE',
                'transactiondate' => now()->format('Y-m-d'),
                'sourceplots' => json_encode($sourcePlots),
                'resultplots' => json_encode([$resultPlot]),
                'sourcebatches' => json_encode($sourceBatchNos),
                'resultbatches' => json_encode([$newBatchNo]),
                'areamap' => json_encode([$resultPlot => $totalArea]),
                'dominantplot' => $dominantPlot,
                'splitmergedreason' => $reason,
                'inputby' => Auth::user()->userid,
                'createdat' => now()
            ]);
            
            $approvalMaster = DB::table('approval')
                ->where('companycode', $companycode)
                ->where('category', 'Approval Rekonstruksi Plot')
                ->first();
            
            if (!$approvalMaster) {
                throw new \Exception('Approval master belum di-setup');
            }
            
            $approvalNo = $this->generateApprovalNo($companycode, now());
            
            DB::table('approvaltransaction')->insert([
                'approvalno' => $approvalNo,
                'companycode' => $companycode,
                'approvalcategoryid' => $approvalMaster->id,
                'transactionnumber' => $transactionNumber,
                'jumlahapproval' => $approvalMaster->jumlahapproval,
                'approval1idjabatan' => $approvalMaster->idjabatanapproval1,
                'approval2idjabatan' => $approvalMaster->idjabatanapproval2,
                'approval3idjabatan' => $approvalMaster->idjabatanapproval3,
                'approvalstatus' => null,
                'inputby' => Auth::user()->userid,
                'createdat' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Merge request berhasil dibuat [{$transactionNumber}]. Menunggu approval.",
                'data' => [
                    'transaction_number' => $transactionNumber,
                    'approval_number' => $approvalNo,
                    'status' => 'PENDING_APPROVAL'
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Merge request failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat merge request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getBatchDetails($batchno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $batch = DB::table('batch as b')
                ->leftJoin('masterlist as m', function($join) use ($companycode) {
                    $join->on('b.plot', '=', 'm.plot')->where('m.companycode', '=', $companycode);
                })
                ->where('b.companycode', $companycode)
                ->where('b.batchno', $batchno)
                ->select([
                    'b.*', 'm.blok',
                    DB::raw("DATE_FORMAT(b.batchdate, '%d/%m/%Y') as formatted_batchdate"),
                    DB::raw("DATE_FORMAT(b.tanggalpanen, '%d/%m/%Y') as formatted_tanggalpanen")
                ])
                ->first();
            
            if (!$batch) {
                return response()->json(['success' => false, 'message' => 'Batch tidak ditemukan'], 404);
            }
            
            $lastActivities = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', function($join) use ($companycode) {
                    $join->on('ldp.lkhno', '=', 'lh.lkhno')->where('lh.companycode', '=', $companycode);
                })
                ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
                ->where('ldp.companycode', $companycode)
                ->where('ldp.batchno', $batchno)
                ->select(['lh.lkhdate', 'a.activityname', 'ldp.luashasil', DB::raw("DATE_FORMAT(lh.lkhdate, '%d/%m/%Y') as formatted_date")])
                ->orderBy('lh.lkhdate', 'desc')
                ->limit(5)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'batch' => $batch,
                    'last_activities' => $lastActivities,
                    'can_split' => $batch->isactive == 1,
                    'can_merge' => $batch->isactive == 1
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error getting batch details: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail batch'], 500);
        }
    }
    
    private function getAndValidateBatch($companycode, $batchno)
    {
        $batch = DB::table('batch')->where('companycode', $companycode)->where('batchno', $batchno)->first();
        
        if (!$batch) throw new \Exception("Batch {$batchno} tidak ditemukan");
        if ($batch->isactive != 1) throw new \Exception("Batch {$batchno} sudah tidak aktif");
        
        return $batch;
    }
    
    private function generateBatchNo($companycode, $date, $offset = 0)
    {
        $dateStr = $date->format('ymd');
        $sequence = DB::table('batch')->where('companycode', $companycode)->whereDate('batchdate', $date)->count() + 1 + $offset;
        return "BATCH{$dateStr}" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
    
    private function generateTransactionNumber($companycode, $date)
    {
        $dateStr = $date->format('ymd');
        $sequence = DB::table('plottransaction')->where('companycode', $companycode)->whereDate('transactiondate', $date)->count() + 1;
        return "SM{$dateStr}" . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }
    
    private function generateApprovalNo($companycode, $date)
    {
        $dateStr = $date->format('ymd');
        $sequence = DB::table('approvaltransaction')->where('companycode', $companycode)->whereDate('createdat', $date)->count() + 1;
        return "APV{$dateStr}" . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    public function getApprovalDetail($approvalno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $approval = DB::table('approvaltransaction as at')
                ->join('approval as am', 'at.approvalcategoryid', '=', 'am.id')
                ->leftJoin('jabatan as j1', 'at.approval1idjabatan', '=', 'j1.idjabatan')
                ->leftJoin('jabatan as j2', 'at.approval2idjabatan', '=', 'j2.idjabatan')
                ->leftJoin('jabatan as j3', 'at.approval3idjabatan', '=', 'j3.idjabatan')
                ->leftJoin('user as u1', 'at.approval1userid', '=', 'u1.userid')
                ->leftJoin('user as u2', 'at.approval2userid', '=', 'u2.userid')
                ->leftJoin('user as u3', 'at.approval3userid', '=', 'u3.userid')
                ->where('at.companycode', $companycode)
                ->where('at.approvalno', $approvalno)
                ->select([
                    'at.*',
                    'am.category',
                    'j1.namajabatan as jabatan1_name',
                    'j2.namajabatan as jabatan2_name',
                    'j3.namajabatan as jabatan3_name',
                    'u1.name as approval1_username',
                    'u2.name as approval2_username',
                    'u3.name as approval3_username',
                    DB::raw("DATE_FORMAT(at.approval1date, '%d/%m/%Y %H:%i') as approval1date"),
                    DB::raw("DATE_FORMAT(at.approval2date, '%d/%m/%Y %H:%i') as approval2date"),
                    DB::raw("DATE_FORMAT(at.approval3date, '%d/%m/%Y %H:%i') as approval3date")
                ])
                ->first();
            
            if (!$approval) {
                return response()->json(['success' => false, 'message' => 'Approval tidak ditemukan'], 404);
            }
            
            return response()->json(['success' => true, 'data' => $approval]);
            
        } catch (\Exception $e) {
            Log::error("Error getting approval detail: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail approval'], 500);
        }
    }

    public function checkPlotExists(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Validasi companycode
            if (empty($companycode)) {
                return response()->json([
                    'exists' => false,
                    'error' => true,
                    'message' => 'Session expired. Silakan refresh halaman.'
                ], 401);
            }
            
            $plotName = strtoupper(trim($request->input('plot', '')));
            
            if (empty($plotName)) {
                return response()->json(['exists' => false]);
            }
            
            // CEK DI MASTERLIST AJA (source of truth)
            $exists = DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('plot', $plotName)
                ->exists();
            
            return response()->json([
                'exists' => $exists,
                'plot' => $plotName
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking plot: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'exists' => false,
                'error' => true,
                'message' => 'Error saat validasi plot'
            ], 500);
        }
    }
}