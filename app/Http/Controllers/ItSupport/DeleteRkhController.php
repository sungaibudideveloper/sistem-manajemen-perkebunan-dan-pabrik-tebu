<?php

namespace App\Http\Controllers\ItSupport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DeleteRkhController extends Controller
{
    /**
     * Display main page (simple search form only)
     */
    public function index()
    {
        return view('it-support.delete-rkh.index', [
            'title' => 'Delete RKH',
            'navbar' => 'IT Support',
            'nav' => 'Delete RKH',
        ]);
    }

    /**
     * Search RKH and get preview (AJAX)
     */
    public function search(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string'
        ]);

        try {
            $companycode = Session::get('companycode');
            $rkhno = $request->rkhno;
            
            // Get RKH basic info
            $rkhInfo = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->first();
            
            if (!$rkhInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKH tidak ditemukan'
                ], 404);
            }
            
            // Generate LKH pattern
            $lkhPattern = str_replace('RKH', 'LKH', $rkhno) . '-%';
            
            // Count affected records
            $impact = [
                'rkhhdr' => DB::table('rkhhdr')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'rkhlst' => DB::table('rkhlst')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'rkhlstworker' => DB::table('rkhlstworker')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'rkhlstkendaraan' => DB::table('rkhlstkendaraan')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'lkhhdr' => DB::table('lkhhdr')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailplot' => DB::table('lkhdetailplot as ldp')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldp.lkhno', '=', 'lh.lkhno')
                             ->on('ldp.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailworker' => DB::table('lkhdetailworker as ldw')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldw.lkhno', '=', 'lh.lkhno')
                             ->on('ldw.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailkendaraan' => DB::table('lkhdetailkendaraan as ldk')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldk.lkhno', '=', 'lh.lkhno')
                             ->on('ldk.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailmaterial' => DB::table('lkhdetailmaterial as ldm')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldm.lkhno', '=', 'lh.lkhno')
                             ->on('ldm.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailbsm' => DB::table('lkhdetailbsm as ldb')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldb.lkhno', '=', 'lh.lkhno')
                             ->on('ldb.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'usematerialhdr' => DB::table('usematerialhdr')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'usemateriallst' => DB::table('usemateriallst')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'suratjalanpos' => DB::table('suratjalanpos')
                    ->where('companycode', $companycode)
                    ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
                    ->count(),
                    
                'timbanganpayload' => DB::table('timbanganpayload')
                    ->where('companycode', $companycode)
                    ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
                    ->count(),
            ];
            
            // Check critical impacts
            $hasCriticalImpact = (
                $impact['usematerialhdr'] > 0 || 
                $impact['usemateriallst'] > 0 || 
                $impact['suratjalanpos'] > 0 || 
                $impact['timbanganpayload'] > 0
            );
            
            // Format dates
            $rkhInfo->formatted_date = $rkhInfo->rkhdate ? date('d M Y', strtotime($rkhInfo->rkhdate)) : '-';
            $rkhInfo->formatted_createdat = $rkhInfo->createdat ? date('d M Y H:i', strtotime($rkhInfo->createdat)) : '-';
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rkhInfo' => $rkhInfo,
                    'impact' => $impact,
                    'hasCriticalImpact' => $hasCriticalImpact,
                    'hasmaterialimpact' => ($impact['usematerialhdr'] > 0 || $impact['usemateriallst'] > 0) ? 1 : 0,
                    'hassuratjalanimpact' => $impact['suratjalanpos'] > 0 ? 1 : 0,
                    'hastimbanganimpact' => $impact['timbanganpayload'] > 0 ? 1 : 0,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Search RKH error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete RKH with audit log
     */
    public function destroy(Request $request, $rkhno)
    {
        $request->validate([
            'deletionreason' => 'required|string|max:1000',
            'confirmation' => 'required|string|in:hapus aman',
        ], [
            'confirmation.in' => 'Konfirmasi tidak sesuai. Ketik "hapus aman" untuk melanjutkan.',
        ]);
        
        DB::beginTransaction();
        
        try {
            $companycode = Session::get('companycode');
            $userid = Auth::user()->userid;
            
            // Get RKH info and impact data for audit
            $searchResponse = $this->search(new Request(['rkhno' => $rkhno]));
            $searchData = json_decode($searchResponse->getContent());
            
            if (!$searchData->success) {
                throw new \Exception('RKH tidak ditemukan');
            }
            
            $data = $searchData->data;
            
            // Insert audit log BEFORE deletion
            DB::table('rkhauditlog')->insert([
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'rkhdate' => $data->rkhInfo->rkhdate,
                'affectedtablessummary' => json_encode($data->impact),
                'hasmaterialimpact' => $data->hasmaterialimpact,
                'hassuratjalanimpact' => $data->hassuratjalanimpact,
                'hastimbanganimpact' => $data->hastimbanganimpact,
                'deletionreason' => $request->deletionreason,
                'deletedby' => $userid,
                'deletedat' => now(),
            ]);
            
            // Execute deletion
            $lkhPattern = str_replace('RKH', 'LKH', $rkhno) . '-%';
            
            // 1. Timbangan payload
            DB::table('timbanganpayload')
                ->where('companycode', $companycode)
                ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
                ->delete();
            
            // 2. Surat jalan pos
            DB::table('suratjalanpos')
                ->where('companycode', $companycode)
                ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
                ->delete();
            
            // 3. Use material lst
            $lkhNos = DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->pluck('lkhno');
            
            if ($lkhNos->isNotEmpty()) {
                DB::table('usemateriallst')
                    ->where('companycode', $companycode)
                    ->whereIn('lkhno', $lkhNos)
                    ->delete();
            }
            
            // 4. Use material hdr
            DB::table('usematerialhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            // 5-9. LKH detail tables
            if ($lkhNos->isNotEmpty()) {
                DB::table('lkhdetailbsm')
                    ->where('companycode', $companycode)
                    ->whereIn('lkhno', $lkhNos)
                    ->delete();
                
                DB::table('lkhdetailmaterial')
                    ->where('companycode', $companycode)
                    ->whereIn('lkhno', $lkhNos)
                    ->delete();
                
                DB::table('lkhdetailkendaraan')
                    ->where('companycode', $companycode)
                    ->whereIn('lkhno', $lkhNos)
                    ->delete();
                
                DB::table('lkhdetailworker')
                    ->where('companycode', $companycode)
                    ->whereIn('lkhno', $lkhNos)
                    ->delete();
                
                DB::table('lkhdetailplot')
                    ->where('companycode', $companycode)
                    ->whereIn('lkhno', $lkhNos)
                    ->delete();
            }
            
            // 10. LKH hdr
            DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            // 11-13. RKH detail tables
            DB::table('rkhlstkendaraan')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            DB::table('rkhlstworker')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            // 14. RKH hdr
            DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "RKH {$rkhno} berhasil dihapus dan tercatat di audit log"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Delete RKH error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus RKH: ' . $e->getMessage()
            ], 500);
        }
    }
}