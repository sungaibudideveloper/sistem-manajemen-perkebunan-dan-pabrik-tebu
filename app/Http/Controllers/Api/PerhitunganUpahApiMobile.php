<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Upah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerhitunganUpahApiMobile extends Controller
{
    /**
     * Calculate wage and insert/update to database
     * Android team requirement: single API call with direct insert
     * 
     * POST /api/mobile/insert-worker-wage
     */
    public function insertWorkerWage(Request $request)
    {
        try {
            $validated = $request->validate([
                'companycode' => 'required|string|max:4',
                'lkhno' => 'required|string',
                'tenagakerjaid' => 'required|string',
                'tenagakerjaurutan' => 'required|integer',
                'activitycode' => 'required|string',
                'jenistenagakerja' => 'required|integer|in:1,2',
                'lkhdate' => 'required|date',
                'jammulai' => 'required|date_format:H:i:s',
                'jamselesai' => 'required|date_format:H:i:s',
                'overtimehours' => 'nullable|numeric|min:0',
                'luashasil' => 'nullable|numeric|min:0', // for borongan
                'keterangan' => 'nullable|string|max:255',
            ]);
            
            // Calculate work hours
            $totalJamKerja = $this->calculateWorkHours($validated['jammulai'], $validated['jamselesai']);
            
            // Calculate wage
            $wageData = $this->calculateWage($validated, $totalJamKerja);
            
            // Check if record exists first
            $existingRecord = DB::table('lkhdetailworker')
                ->where('companycode', $validated['companycode'])
                ->where('lkhno', $validated['lkhno'])
                ->where('tenagakerjaid', $validated['tenagakerjaid'])
                ->first();

            if (!$existingRecord) {
                return response()->json([
                    'status' => 0,
                    'description' => 'Error, Please ensure the worker is assigned to this LKH first'
                ], 404);
            }

            // Update existing record only
            $updated = DB::table('lkhdetailworker')
                ->where('companycode', $validated['companycode'])
                ->where('lkhno', $validated['lkhno'])
                ->where('tenagakerjaid', $validated['tenagakerjaid'])
                ->update([
                    'tenagakerjaurutan' => $validated['tenagakerjaurutan'],
                    'jammasuk' => $validated['jammulai'],
                    'jamselesai' => $validated['jamselesai'],
                    'totaljamkerja' => $totalJamKerja,
                    'overtimehours' => $validated['overtimehours'] ?? 0,
                    
                    // Calculated wage fields
                    'premi' => $wageData['premi'],
                    'upahharian' => $wageData['upahharian'],
                    'upahperjam' => $wageData['upahperjam'],
                    'upahlembur' => $wageData['upahlembur'],
                    'upahborongan' => $wageData['upahborongan'],
                    'totalupah' => $wageData['totalupah'],
                    
                    'keterangan' => $validated['keterangan'] ?? 'Mobile upload',
                    'updatedat' => now()
                ]);

            if ($updated) {
                // ✅ SYNC HEADER SETELAH UPDATE BERHASIL
                $this->syncLkhHeaderTotals($validated['companycode'], $validated['lkhno']);
                
                return response()->json([
                    'status' => 1,
                    'description' => 'Worker wage updated successfully',
                    'data' => [
                        'total_upah' => $wageData['totalupah'],
                        'jam_kerja' => $totalJamKerja,
                        'breakdown' => [
                            'upah_harian' => $wageData['upahharian'],
                            'upah_perjam' => $wageData['upahperjam'],
                            'upah_lembur' => $wageData['upahlembur'],
                            'upah_borongan' => $wageData['upahborongan'],
                            'premi' => $wageData['premi']
                        ]
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => 0,
                    'description' => 'Failed to update worker wage No changes were made to the record'
                ], 400);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'description' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Error in insertWorkerWage API', [
                'description' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 0,
                'description' => 'Update failed: ' . (config('app.debug') ? $e->getMessage() : 'Internal server error')
            ], 500);
        }
    }

    /**
     * ✅ SYNC LKH HEADER TOTALS DENGAN DETAIL DATA
     * Method baru untuk sinkronisasi header
     */
    private function syncLkhHeaderTotals($companycode, $lkhno)
    {
        try {
            // Hitung total dari lkhdetailworker
            $workerTotals = DB::table('lkhdetailworker')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->selectRaw('COUNT(*) as total_workers, SUM(totalupah) as total_upah')
                ->first();

            // Hitung total dari lkhdetailplot (hasil dan sisa)
            $plotTotals = DB::table('lkhdetailplot')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->selectRaw('SUM(luashasil) as total_hasil, SUM(luassisa) as total_sisa')
                ->first();
            
            // Update header lkhhdr
            $updateResult = DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->update([
                    'totalworkers' => $workerTotals->total_workers ?? 0,
                    'totalupahall' => $workerTotals->total_upah ?? 0,
                    'totalhasil' => $plotTotals->total_hasil ?? 0,
                    'totalsisa' => $plotTotals->total_sisa ?? 0,
                    'updatedat' => now()
                ]);

            // Log untuk debugging
            \Log::info('LKH Header synced via API', [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'totalworkers' => $workerTotals->total_workers ?? 0,
                'totalupahall' => $workerTotals->total_upah ?? 0,
                'totalhasil' => $plotTotals->total_hasil ?? 0,
                'updated_rows' => $updateResult
            ]);

        } catch (\Exception $e) {
            \Log::error('Error syncing LKH header totals', [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'error' => $e->getMessage()
            ]);
            
            // Tidak throw exception agar API tetap return success
            // karena update worker sudah berhasil
        }
    }
    
    /**
     * Calculate wage based on validated input
     */
    private function calculateWage($data, $totalJamKerja)
    {
        $activityGroup = $this->getActivityGroupFromCode($data['activitycode']);
        $dayType = $this->getDayType($data['lkhdate']);
        
        $wageData = [
            'premi' => 0,
            'upahharian' => 0,
            'upahperjam' => 0,
            'upahlembur' => 0,
            'upahborongan' => 0,
            'totalupah' => 0
        ];
        
        if ($data['jenistenagakerja'] == 1) {
            // Harian calculation
            if ($totalJamKerja >= 8) {
                $dailyRate = Upah::getCurrentRate($data['companycode'], $activityGroup, $dayType, $data['lkhdate']);
                $wageData['upahharian'] = $dailyRate ?: 115722.8;
            } else {
                $hourlyRate = Upah::getCurrentRate($data['companycode'], $activityGroup, 'HOURLY', $data['lkhdate']);
                $wageData['upahperjam'] = $hourlyRate ?: 16532;
                $wageData['upahharian'] = $totalJamKerja * $wageData['upahperjam'];
            }
            
            // Overtime
            $overtimeHours = $data['overtimehours'] ?? 0;
            if ($overtimeHours > 0) {
                $overtimeRate = Upah::getCurrentRate($data['companycode'], $activityGroup, 'OVERTIME', $data['lkhdate']);
                $wageData['upahlembur'] = $overtimeHours * ($overtimeRate ?: 12542);
            }
            
            $wageData['totalupah'] = $wageData['upahharian'] + $wageData['upahlembur'];
            
        } else {
            // Borongan calculation
            $wageType = $this->getBoronganWageType($data['activitycode']);
            $boronganRate = Upah::getCurrentRate($data['companycode'], $activityGroup, $wageType, $data['lkhdate']);
            
            if ($wageType === 'PER_HECTARE' && isset($data['luashasil'])) {
                $wageData['upahborongan'] = $boronganRate * $data['luashasil'];
            } else {
                $wageData['upahborongan'] = $boronganRate ?: 140000;
            }
            
            $wageData['totalupah'] = $wageData['upahborongan'];
        }
        
        return $wageData;
    }
    
    // Helper methods (same as before)
    private function calculateWorkHours($jamMasuk, $jamSelesai)
    {
        $start = Carbon::createFromFormat('H:i:s', $jamMasuk);
        $end = Carbon::createFromFormat('H:i:s', $jamSelesai);
        
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        // Total hours including break
        $totalHours = $start->diffInHours($end, false);
        
        // Define break time
        $breakStart = Carbon::createFromFormat('H:i:s', '12:00:00');
        $breakEnd = Carbon::createFromFormat('H:i:s', '13:00:00');
        
        // Check if work period overlaps with break time
        $breakDeduction = 0;
        
        if ($start->lt($breakEnd) && $end->gt($breakStart)) {
            // Work period overlaps with break
            $overlapStart = $start->gt($breakStart) ? $start : $breakStart;
            $overlapEnd = $end->lt($breakEnd) ? $end : $breakEnd;
            
            // Calculate break overlap in hours
            $breakDeduction = $overlapStart->diffInHours($overlapEnd, false);
        }
        
        return $totalHours - $breakDeduction;
    }
    
    private function getDayType($workDate)
    {
        $dayOfWeek = Carbon::parse($workDate)->dayOfWeek;
        
        if ($dayOfWeek === Carbon::SATURDAY) {
            return 'WEEKEND_SATURDAY';
        } elseif ($dayOfWeek === Carbon::SUNDAY) {
            return 'WEEKEND_SUNDAY';
        }
        
        return 'DAILY';
    }
    
    private function getActivityGroupFromCode($activitycode)
    {
        if (preg_match('/^([IVX]+)/', $activitycode, $matches)) {
            return $matches[1];
        }
        return 'V';
    }
    
    private function getBoronganWageType($activitycode)
    {
        if (strpos($activitycode, 'VI') === 0) {
            return 'PER_KG';
        }
        return 'PER_HECTARE';
    }
}