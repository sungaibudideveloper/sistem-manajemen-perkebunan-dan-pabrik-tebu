<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Domain;

use Illuminate\Support\Facades\DB;

/**
 * KendaraanRepository
 * 
 * Handles rkhlstkendaraan table operations.
 * RULE: All kendaraan assignment queries here.
 */
class KendaraanRepository
{
    /**
     * Get kendaraan grouped by activity for RKH
     * Returns collection grouped by activitycode
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return \Illuminate\Support\Collection
     */
    public function getKendaraanByActivity($companycode, $rkhno)
    {
        return DB::table('rkhlstkendaraan as rk')
            ->leftJoin('kendaraan as k', 'rk.kendaraanid', '=', 'k.id')
            ->leftJoin('tenagakerja as tk_operator', function($join) use ($companycode) {
                $join->on('rk.operatorid', '=', 'tk_operator.tenagakerjaid')
                    ->where('tk_operator.companycode', '=', $companycode);
            })
            ->leftJoin('tenagakerja as tk_helper', function($join) use ($companycode) {
                $join->on('rk.helperid', '=', 'tk_helper.tenagakerjaid')
                    ->where('tk_helper.companycode', '=', $companycode);
            })
            ->leftJoin('activity as a', 'rk.activitycode', '=', 'a.activitycode')
            ->where('rk.companycode', $companycode)
            ->where('rk.rkhno', $rkhno)
            ->select([
                'rk.activitycode',
                'a.activityname',
                'rk.nokendaraan',
                'k.jenis as vehicle_type',
                'rk.operatorid',
                'tk_operator.nama as operator_nama',
                'tk_operator.nik as operator_nik',
                'rk.usinghelper',
                'rk.helperid',
                'tk_helper.nama as helper_nama',
                'rk.urutan'
            ])
            ->orderBy('rk.activitycode')
            ->orderBy('rk.urutan')
            ->get()
            ->groupBy('activitycode');
    }

    /**
     * Get all vehicles with their operators (for dropdown)
     * 
     * @param string $companycode
     * @return \Illuminate\Support\Collection
     */
    public function getVehiclesWithOperators($companycode)
    {
        return DB::table('kendaraan as k')
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('k.idtenagakerja', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode)
                    ->where('tk.jenistenagakerja', '=', 3) // Operator
                    ->where('tk.isactive', '=', 1);
            })
            ->where('k.companycode', $companycode)
            ->where('k.isactive', 1)
            ->select([
                'k.nokendaraan',
                'k.jenis as vehicle_type',
                'k.idtenagakerja as operator_id',
                'tk.nama as operator_name',
                'tk.nik as operator_nik'
            ])
            ->orderBy('k.jenis')
            ->orderBy('k.nokendaraan')
            ->get();
    }

    /**
     * Delete all kendaraan for RKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return int
     */
    public function deleteByRkhNo($companycode, $rkhno)
    {
        return DB::table('rkhlstkendaraan')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
    }

    /**
     * Bulk insert kendaraan
     * 
     * @param array $rows
     * @return bool
     */
    public function insertKendaraan(array $rows)
    {
        if (empty($rows)) {
            return false;
        }

        try {
            DB::table('rkhlstkendaraan')->insert($rows);
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to insert kendaraan assignments", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Replace kendaraan for RKH (delete + insert)
     * Used in create/update operations
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param int $rkhhdrid
     * @param array $kendaraan Format: [activitycode => [[nokendaraan, operatorid, helperid, ...]]]
     * @return void
     */
    public function replaceKendaraanForRkh($companycode, $rkhno, $rkhhdrid, array $kendaraan)
    {
        // Delete existing
        $this->deleteByRkhNo($companycode, $rkhno);
        
        if (empty($kendaraan)) {
            return;
        }
        
        // Build insert data
        $records = [];
        
        foreach ($kendaraan as $activityCode => $vehicles) {
            // ✅ FIX: Reset counter per activity
            $urutan = 1;
            
            foreach ($vehicles as $vehicle) {  // ✅ Don't use $index
                // Skip if no kendaraan/operator selected
                if (empty($vehicle['nokendaraan']) || empty($vehicle['operatorid'])) {
                    continue;
                }
                
                // Get kendaraanid from database
                $kendaraanData = DB::table('kendaraan')
                    ->where('companycode', $companycode)
                    ->where('nokendaraan', $vehicle['nokendaraan'])
                    ->select('id')
                    ->first();
                
                // Validasi kendaraan exist
                if (!$kendaraanData) {
                    \Log::warning("Kendaraan not found", [
                        'nokendaraan' => $vehicle['nokendaraan'],
                        'activitycode' => $activityCode,
                        'rkhno' => $rkhno
                    ]);
                    continue;
                }

                $records[] = [
                    'companycode' => $companycode,
                    'rkhno' => $rkhno,
                    'rkhhdrid' => $rkhhdrid,
                    'activitycode' => $activityCode,
                    'nokendaraan' => $vehicle['nokendaraan'],
                    'kendaraanid' => $kendaraanData->id,
                    'operatorid' => $vehicle['operatorid'],
                    'usinghelper' => $vehicle['usinghelper'] ?? 0,
                    'helperid' => $vehicle['helperid'] ?? null,
                    'urutan' => $urutan,  // ✅ Use local counter
                    'createdat' => now()
                ];
                
                $urutan++;  // ✅ Increment only after valid insert
            }
        }
        
        if (!empty($records)) {
            $this->insertKendaraan($records);
            
            \Log::info("Kendaraan assignments created", [
                'rkhno' => $rkhno,
                'rkhhdrid' => $rkhhdrid,
                'total_vehicles' => count($records),
            ]);
        }
    }
}