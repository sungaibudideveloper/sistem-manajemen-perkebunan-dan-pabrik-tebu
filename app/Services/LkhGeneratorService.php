<?php

namespace App\Services;

use App\Models\Rkhhdr;
use App\Models\Rkhlst;
use App\Models\Lkhhdr;
use App\Models\Lkhlst;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LkhGeneratorService
{
    /**
     * Generate LKH from fully approved RKH
     * 
     * @param string $rkhno
     * @return array
     */
    public function generateLkhFromRkh($rkhno)
    {
        try {
            DB::beginTransaction();

            // 1. Validasi RKH exist dan sudah fully approved
            $rkh = Rkhhdr::where('rkhno', $rkhno)->first();
            if (!$rkh) {
                throw new \Exception("RKH {$rkhno} not found");
            }

            if (!$this->isRkhFullyApproved($rkh)) {
                throw new \Exception("RKH {$rkhno} belum fully approved");
            }

            // 2. Cek apakah LKH sudah pernah di-generate
            $existingLkh = Lkhhdr::where('rkhno', $rkhno)->exists();
            if ($existingLkh) {
                throw new \Exception("LKH untuk RKH {$rkhno} sudah pernah di-generate");
            }

            // 3. Ambil detail aktivitas dari RKH
            $rkhActivities = Rkhlst::where('rkhno', $rkhno)
                ->where('companycode', $rkh->companycode)
                ->get();

            if ($rkhActivities->isEmpty()) {
                throw new \Exception("Tidak ada aktivitas ditemukan untuk RKH {$rkhno}");
            }

            $generatedLkh = [];
            $lkhIndex = 1;

            // 4. Generate LKH untuk setiap aktivitas
            foreach ($rkhActivities as $activity) {
                $lkhno = $this->generateLkhNumber($rkhno, $lkhIndex);
                
                // Buat LKH Header
                $lkhHeader = Lkhhdr::create([
                    'lkhno' => $lkhno,
                    'rkhno' => $rkhno,
                    'companycode' => $rkh->companycode,
                    'activitycode' => $activity->activitycode,
                    'blok' => $activity->blok,
                    'plot' => $activity->plot,
                    'mandorid' => $rkh->mandorid,
                    'lkhdate' => $rkh->rkhdate,
                    'jenistenagakerja' => $activity->jenistenagakerja,
                    'totalworkers' => 0,
                    'totalluasactual' => 0.00,
                    'totalhasil' => 0.00,
                    'totalsisa' => $activity->luasarea, // Sisa = luas area awal
                    'totalupahall' => 0.00,
                    'jammulaikerja' => null,
                    'jamselesaikerja' => null,
                    'totalovertimehours' => 0.00,
                    'status' => 'DRAFT',
                    'keterangan' => "Auto-generated from RKH {$rkhno}",
                    'inputby' => auth()->user()->userid ?? 'SYSTEM',
                    'createdat' => now(),
                ]);

                $generatedLkh[] = [
                    'lkhno' => $lkhno,
                    'activitycode' => $activity->activitycode,
                    'blok' => $activity->blok,
                    'plot' => $activity->plot,
                    'jenistenagakerja' => $activity->jenistenagakerja,
                    'status' => 'DRAFT'
                ];

                $lkhIndex++;
            }

            DB::commit();

            Log::info("LKH auto-generated for RKH {$rkhno}", [
                'generated_lkh' => $generatedLkh,
                'total_lkh' => count($generatedLkh)
            ]);

            return [
                'success' => true,
                'message' => 'LKH berhasil di-generate otomatis',
                'generated_lkh' => $generatedLkh,
                'total_lkh' => count($generatedLkh)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to generate LKH for RKH {$rkhno}: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'generated_lkh' => [],
                'total_lkh' => 0
            ];
        }
    }

    /**
     * Generate LKH number based on RKH number and index
     * Format: LKH{DDMM}{XX}{YY}-{INDEX}
     * 
     * @param string $rkhno
     * @param int $index
     * @return string
     */
    private function generateLkhNumber($rkhno, $index)
    {
        // RKH format: RKH19061325
        // LKH format: LKH19061325-1
        $rkhPart = substr($rkhno, 3); // Remove "RKH" prefix
        return "LKH{$rkhPart}-{$index}";
    }

    /**
     * Check if RKH is fully approved
     * 
     * @param Rkhhdr $rkh
     * @return bool
     */
    private function isRkhFullyApproved($rkh)
    {
        // Jika tidak ada requirement approval, anggap sudah approved
        if (!$rkh->jumlahapproval || $rkh->jumlahapproval == 0) {
            return true;
        }

        // Check berdasarkan jumlah approval yang diperlukan
        switch ($rkh->jumlahapproval) {
            case 1:
                return $rkh->approval1flag === '1';
            case 2:
                return $rkh->approval1flag === '1' && $rkh->approval2flag === '1';
            case 3:
                return $rkh->approval1flag === '1' && 
                       $rkh->approval2flag === '1' && 
                       $rkh->approval3flag === '1';
            default:
                return false;
        }
    }

    /**
     * Get LKH summary for specific RKH
     * 
     * @param string $rkhno
     * @return array
     */
    public function getLkhSummaryForRkh($rkhno)
    {
        $lkhList = Lkhhdr::where('rkhno', $rkhno)
            ->with(['activity'])
            ->get();

        $summary = [
            'total_lkh' => $lkhList->count(),
            'by_status' => $lkhList->groupBy('status')->map(function ($group) {
                return $group->count();
            })->toArray(),
            'by_jenistenaga' => $lkhList->groupBy('jenistenagakerja')->map(function ($group) {
                return $group->count();
            })->toArray(),
            'details' => $lkhList->map(function ($lkh) {
                return [
                    'lkhno' => $lkh->lkhno,
                    'activitycode' => $lkh->activitycode,
                    'activityname' => $lkh->activity->activityname ?? 'Unknown',
                    'blok' => $lkh->blok,
                    'plot' => $lkh->plot,
                    'status' => $lkh->status,
                    'jenistenagakerja' => $lkh->jenistenagakerja,
                    'totalworkers' => $lkh->totalworkers,
                    'totalhasil' => $lkh->totalhasil,
                    'totalsisa' => $lkh->totalsisa,
                ];
            })->toArray()
        ];

        return $summary;
    }

    /**
     * Bulk generate LKH for multiple RKH
     * 
     * @param array $rkhList
     * @return array
     */
    public function bulkGenerateLkh($rkhList)
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($rkhList as $rkhno) {
            $result = $this->generateLkhFromRkh($rkhno);
            $results[$rkhno] = $result;
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'message' => "Bulk generate completed: {$successCount} success, {$failCount} failed",
            'results' => $results,
            'summary' => [
                'total_processed' => count($rkhList),
                'success_count' => $successCount,
                'fail_count' => $failCount
            ]
        ];
    }

    /**
     * Regenerate LKH (untuk kasus khusus)
     * 
     * @param string $rkhno
     * @param bool $forceRegenerate
     * @return array
     */
    public function regenerateLkh($rkhno, $forceRegenerate = false)
    {
        if (!$forceRegenerate) {
            throw new \Exception("Regenerate LKH hanya bisa dilakukan dengan force flag");
        }

        try {
            DB::beginTransaction();

            // Hapus LKH yang sudah ada
            $existingLkh = Lkhhdr::where('rkhno', $rkhno)->get();
            foreach ($existingLkh as $lkh) {
                // Hapus detail terlebih dahulu
                Lkhlst::where('lkhno', $lkh->lkhno)->delete();
                // Hapus header
                $lkh->delete();
            }

            DB::commit();

            // Generate ulang
            return $this->generateLkhFromRkh($rkhno);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}