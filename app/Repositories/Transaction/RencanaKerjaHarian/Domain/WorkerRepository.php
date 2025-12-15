<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Domain;

use Illuminate\Support\Facades\DB;

/**
 * WorkerRepository
 * 
 * Handles rkhlstworker table operations.
 * RULE: All worker assignment queries here.
 */
class WorkerRepository
{
    /**
     * Get workers grouped by activity for RKH
     * Returns collection with worker counts per activity
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return \Illuminate\Support\Collection
     */
    public function getWorkersByActivityForRkh($companycode, $rkhno)
    {
        return DB::table('rkhlstworker as w')
            ->leftJoin('activity as a', 'w.activitycode', '=', 'a.activitycode')
            ->leftJoin('jenistenagakerja as j', 'a.jenistenagakerja', '=', 'j.idjenistenagakerja')
            ->where('w.companycode', $companycode)
            ->where('w.rkhno', $rkhno)
            ->select([
                'w.activitycode',
                'a.activityname',
                'a.jenistenagakerja',
                'j.nama as jenis_nama',
                'w.jumlahlaki',
                'w.jumlahperempuan',
                'w.jumlahtenagakerja'
            ])
            ->orderBy('w.activitycode')
            ->get();
    }

    public function getHelpersByCompany($companycode)
    {
        return DB::table('tenagakerja')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->where('jenistenagakerja', 4)
            ->select('tenagakerjaid', 'nama', 'nik')
            ->orderBy('nama')
            ->get();
    }

    /**
     * Delete all workers for RKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return int
     */
    public function deleteByRkhNo($companycode, $rkhno)
    {
        return DB::table('rkhlstworker')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
    }

    /**
     * Bulk insert workers
     * 
     * @param array $rows
     * @return bool
     */
    public function insertWorkers(array $rows)
    {
        if (empty($rows)) {
            return false;
        }

        return DB::table('rkhlstworker')->insert($rows);
    }

    /**
     * Replace workers for RKH (delete + insert)
     * Used in create/update operations
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param int $rkhhdrid
     * @param array $workers Format: [activitycode => [jumlahlaki, jumlahperempuan, total]]
     * @return void
     */
    public function replaceWorkersForRkh($companycode, $rkhno, $rkhhdrid, array $workers)
    {
        // Delete existing
        $this->deleteByRkhNo($companycode, $rkhno);
        
        if (empty($workers)) {
            return;
        }
        
        // Build insert data
        $workerRecords = [];
        
        // ✅ FIX: Don't use key as activitycode!
        foreach ($workers as $worker) {  // ✅ Changed: removed $activityCode from foreach
            $workerRecords[] = [
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'rkhhdrid' => $rkhhdrid,
                'activitycode' => $worker['activitycode'],  // ✅ Use from array value
                'jumlahlaki' => $worker['jumlahlaki'] ?? 0,
                'jumlahperempuan' => $worker['jumlahperempuan'] ?? 0,
                'jumlahtenagakerja' => $worker['jumlahtenagakerja'] ?? 0,
                'createdat' => now()
            ];
        }
        
        if (!empty($workerRecords)) {
            $this->insertWorkers($workerRecords);
            
            \Log::info("Worker assignments created", [
                'rkhno' => $rkhno,
                'rkhhdrid' => $rkhhdrid,
                'total_records' => count($workerRecords)
            ]);
        }
    }
}