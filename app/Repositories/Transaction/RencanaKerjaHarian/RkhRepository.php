<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * RkhRepository
 * 
 * Handles all database queries related to RKH (Rencana Kerja Harian)
 * Uses surrogate ID (id) for joins, natural key (rkhno) for business logic
 */
class RkhRepository
{
    /**
     * RkhRepository - FIXED VERSION
     * 
     */

    // =====================================
    // QUERY BUILDERS
    // =====================================

    /**
     * Get RKH list with filters (for index/listing)
     */
    public function getIndexQuery(string $companycode, array $filters = [])
    {
        $query = DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3',
                'r.approvalstatus',
                DB::raw('CASE 
                    WHEN r.approvalstatus = "1" THEN "Approved"
                    WHEN r.approvalstatus = "0" THEN "Rejected"
                    WHEN app.jumlahapproval IS NULL OR app.jumlahapproval = 0 THEN "No Approval Required"
                    WHEN r.approval1flag IS NULL AND app.idjabatanapproval1 IS NOT NULL THEN "Waiting"
                    WHEN r.approval1flag = "0" THEN "Declined"
                    WHEN r.approval1flag = "1" AND app.idjabatanapproval2 IS NOT NULL AND r.approval2flag IS NULL THEN "Waiting"
                    WHEN r.approval2flag = "0" THEN "Declined"
                    WHEN r.approval2flag = "1" AND app.idjabatanapproval3 IS NOT NULL AND r.approval3flag IS NULL THEN "Waiting"
                    WHEN r.approval3flag = "0" THEN "Declined"
                    ELSE "Waiting"
                END as approval_status'),
                DB::raw('CASE 
                    WHEN r.status = "Completed" THEN "Completed"
                    ELSE "In Progress"
                END as current_status')
            ]);

        // Apply filters
        if (!empty($filters['search'])) {
            $query->where('r.rkhno', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['filter_approval'])) {
            $query = $this->applyApprovalFilter($query, $filters['filter_approval']);
        }

        if (!empty($filters['filter_status'])) {
            $query = $this->applyStatusFilter($query, $filters['filter_status']);
        }

        if (empty($filters['all_date'])) {
            $dateToFilter = $filters['filter_date'] ?? Carbon::today()->format('Y-m-d');
            $query->whereDate('r.rkhdate', $dateToFilter);
        }

        return $query->orderBy('r.rkhdate', 'desc')->orderBy('r.rkhno', 'desc');
    }

    /**
     * Get RKH header by surrogate ID
     */
    public function findById(int $id): ?object
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                     ->on('r.companycode', '=', 'app.companycode');
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.id', $id)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3'
            ])
            ->first();
    }

    /**
     * Get RKH header by business key (rkhno)
     */
    public function findByRkhNo(string $companycode, string $rkhno): ?object
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3'
            ])
            ->first();
    }

    /**
     * ✅ FIXED: Get RKH details (rkhlst) - JOIN menggunakan surrogate ID
     */
    public function getDetails(string $companycode, string $rkhno): Collection
    {
        return DB::table('rkhlst as r')
            ->leftJoin('rkhlstworker as w', function($join) {
                $join->on('r.companycode', '=', 'w.companycode')
                     ->on('r.rkhno', '=', 'w.rkhno')
                     ->on('r.activitycode', '=', 'w.activitycode');
            })
            ->leftJoin('herbisidagroup as hg', function($join) {
                $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                    ->on('r.activitycode', '=', 'hg.activitycode');
            })
            ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode')
            // ✅ FIXED: Use batchid instead of batchno
            ->leftJoin('batch as b', 'r.batchid', '=', 'b.id')
            ->leftJoin('masterlist as m', function($join) use ($companycode) {
                $join->on('r.plot', '=', 'm.plot')
                     ->where('m.companycode', '=', $companycode);
            })
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'w.jumlahlaki',
                'w.jumlahperempuan',
                'w.jumlahtenagakerja',
                'hg.herbisidagroupname',
                'a.activityname',
                'a.jenistenagakerja',
                'a.isblokactivity',
                'b.batchno',
                'b.lifecyclestatus as batch_lifecycle',
                'b.batcharea',
                'b.tanggalpanen',
                'm.blok as masterlist_blok',
                DB::raw("CASE 
                    WHEN r.blok = 'ALL' THEN 'Semua Blok'
                    WHEN r.plot IS NULL THEN CONCAT('Blok: ', r.blok)
                    ELSE CONCAT(COALESCE(m.blok, r.blok), '-', r.plot)
                END as location_display")
            ])
            ->orderBy('r.blok')
            ->orderBy('r.plot')
            ->get();
    }

    /**
     * Get worker assignments grouped by activity
     */
    public function getWorkersByActivity(string $companycode, string $rkhno): Collection
    {
        return DB::table('rkhlstworker as w')
            ->leftJoin('activity as a', 'w.activitycode', '=', 'a.activitycode')
            ->where('w.companycode', $companycode)
            ->where('w.rkhno', $rkhno)
            ->select([
                'w.activitycode',
                'a.activityname',
                'w.jumlahlaki',
                'w.jumlahperempuan',
                'w.jumlahtenagakerja'
            ])
            ->orderBy('w.activitycode')
            ->get()
            ->groupBy('activitycode');
    }

    /**
     * ✅ FIXED: Get kendaraan assignments - JOIN menggunakan surrogate ID
     */
    public function getKendaraanByActivity(string $companycode, string $rkhno): Collection
    {
        return DB::table('rkhlstkendaraan as rk')
            // ✅ FIXED: Use kendaraanid instead of nokendaraan
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
                'k.nokendaraan',
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

    // =====================================
    // CREATE/UPDATE/DELETE
    // =====================================

    /**
     * Create RKH header
     */
    public function create(array $data): int
    {
        return DB::table('rkhhdr')->insertGetId($data);
    }

    /**
     * Update by surrogate ID
     */
    public function update(int $id, array $data): bool
    {
        return DB::table('rkhhdr')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Update by business key
     */
    public function updateByRkhNo(string $companycode, string $rkhno, array $data): bool
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update($data);
    }

    /**
     * Delete by surrogate ID
     */
    public function delete(int $id): bool
    {
        return DB::table('rkhhdr')
            ->where('id', $id)
            ->delete();
    }

    /**
     * Delete by business key
     */
    public function deleteByRkhNo(string $companycode, string $rkhno): bool
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
    }

    /**
     * ✅ FIXED: Create RKH details - Pastikan isi batchid & rkhhdrid
     */
    public function createDetails(array $details): bool
    {
        if (empty($details)) {
            return true;
        }

        // Validate that all details have required surrogate IDs
        foreach ($details as $detail) {
            if (isset($detail['batchno']) && !isset($detail['batchid'])) {
                \Log::warning('Creating rkhlst without batchid', $detail);
            }
            if (!isset($detail['rkhhdrid'])) {
                \Log::warning('Creating rkhlst without rkhhdrid', $detail);
            }
        }

        return DB::table('rkhlst')->insert($details);
    }

    /**
     * Delete RKH details
     */
    public function deleteDetails(string $companycode, string $rkhno): int
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
    }

    /**
     * ✅ FIXED: Create worker assignments - Pastikan isi rkhhdrid
     */
    public function createWorkers(array $workers): bool
    {
        if (empty($workers)) {
            return true;
        }

        // Validate rkhhdrid
        foreach ($workers as $worker) {
            if (!isset($worker['rkhhdrid'])) {
                \Log::warning('Creating rkhlstworker without rkhhdrid', $worker);
            }
        }
        
        return DB::table('rkhlstworker')->insert($workers);
    }

    /**
     * Delete worker assignments
     */
    public function deleteWorkers(string $companycode, string $rkhno): int
    {
        return DB::table('rkhlstworker')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
    }

    /**
     * ✅ FIXED: Create kendaraan assignments - Pastikan isi kendaraanid & rkhhdrid
     */
    public function createKendaraan(array $kendaraan): bool
    {
        if (empty($kendaraan)) {
            return true;
        }

        // Validate surrogate IDs
        foreach ($kendaraan as $k) {
            if (isset($k['nokendaraan']) && !isset($k['kendaraanid'])) {
                \Log::warning('Creating rkhlstkendaraan without kendaraanid', $k);
            }
            if (!isset($k['rkhhdrid'])) {
                \Log::warning('Creating rkhlstkendaraan without rkhhdrid', $k);
            }
        }
        
        return DB::table('rkhlstkendaraan')->insert($kendaraan);
    }

    /**
     * Delete kendaraan assignments
     */
    public function deleteKendaraan(string $companycode, string $rkhno): int
    {
        return DB::table('rkhlstkendaraan')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
    }

    /**
     * Get RKH numbers for specific date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getRkhNumbersByDate(string $companycode, string $date): array
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $date)
            ->pluck('rkhno')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get last RKH number for specific date (for sequence generation)
     * WITH LOCK to prevent race condition
     * 
     * @param string $companycode
     * @param string $date
     * @param string $prefix (e.g., "RKH01012")
     * @return object|null
     */
    public function getLastRkhNoForDate(string $companycode, string $date, string $prefix): ?object
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $date)
            ->where('rkhno', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
            ->first();
    }

    /**
     * Check if RKH exists
     */
    public function exists(string $companycode, string $rkhno): bool
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->exists();
    }

    /**
     * Get pending approvals for specific jabatan
     * 
     * @param string $companycode
     * @param int $jabatanId
     * @return Collection
     */
    public function getPendingApprovals(string $companycode, int $jabatanId): Collection
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->where(function($query) use ($jabatanId) {
                $query->where(function($q) use ($jabatanId) {
                    // Level 1 approval
                    $q->where('app.idjabatanapproval1', $jabatanId)
                      ->whereNull('r.approval1flag');
                })->orWhere(function($q) use ($jabatanId) {
                    // Level 2 approval
                    $q->where('app.idjabatanapproval2', $jabatanId)
                      ->where('r.approval1flag', '1')
                      ->whereNull('r.approval2flag');
                })->orWhere(function($q) use ($jabatanId) {
                    // Level 3 approval
                    $q->where('app.idjabatanapproval3', $jabatanId)
                      ->where('r.approval1flag', '1')
                      ->where('r.approval2flag', '1')
                      ->whereNull('r.approval3flag');
                });
            })
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3',
                DB::raw('CASE 
                    WHEN app.idjabatanapproval1 = '.$jabatanId.' AND r.approval1flag IS NULL THEN 1
                    WHEN app.idjabatanapproval2 = '.$jabatanId.' AND r.approval1flag = "1" AND r.approval2flag IS NULL THEN 2
                    WHEN app.idjabatanapproval3 = '.$jabatanId.' AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('r.rkhdate', 'desc')
            ->get();
    }

    /**
     * Get RKH approval detail (with approval history)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return object|null
     */
    public function getApprovalDetail(string $companycode, string $rkhno): ?object
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->leftJoin('user as u1', 'r.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'r.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'r.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'app.idjabatanapproval1', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'app.idjabatanapproval2', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'app.idjabatanapproval3', '=', 'j3.idjabatan')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2', 
                'app.idjabatanapproval3',
                'u1.name as approval1_user_name',
                'u2.name as approval2_user_name',
                'u3.name as approval3_user_name',
                'j1.namajabatan as jabatan1_name',
                'j2.namajabatan as jabatan2_name',
                'j3.namajabatan as jabatan3_name'
            ])
            ->first();
    }

    /**
     * Get outstanding RKH for mandor
     * RKH is considered "outstanding" if status is NOT 'Completed'
     * 
     * @param string $companycode
     * @param string $mandorId
     * @return object|null
     */
    public function getOutstandingRkh(string $companycode, string $mandorId): ?object
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->where('r.companycode', $companycode)
            ->where('r.mandorid', $mandorId)
            ->where(function($query) {
                // Outstanding means: NOT Completed
                $query->where('r.status', '!=', 'Completed')
                    ->orWhereNull('r.status');
            })
            ->select([
                'r.rkhno',
                'r.rkhdate',
                'r.status',
                'r.mandorid',
                'm.name as mandor_name'
            ])
            ->orderBy('r.rkhdate', 'desc')
            ->first();
    }

    /**
     * Check if RKH has planting activities (2.2.7)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function hasPlantingActivities(string $companycode, string $rkhno): bool
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->exists();
    }

    /**
     * Check if RKH needs material usage generation
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function needsMaterialUsage(string $companycode, string $rkhno): bool
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->whereNotNull('herbisidagroupid')
            ->exists();
    }

    /**
     * Get planting plots for RKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return Collection
     */
    public function getPlantingPlots(string $companycode, string $rkhno): Collection
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->get();
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Get RKH date (helper for subqueries)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return string
     */
    private function getRkhDate(string $companycode, string $rkhno): ?string
    {
        $result = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->value('rkhdate');
        
        return $result ? Carbon::parse($result)->format('Y-m-d') : null;
    }

     /**
     * Get LKH count for RKH
     */
    public function getLkhCount(string $companycode, string $rkhno): int
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->count();
    }

    // =====================================
    // FILTER HELPERS
    // =====================================

    private function applyApprovalFilter($query, string $filter)
    {
        switch ($filter) {
            case 'approved':
                return $query->where('r.approvalstatus', '1');
            case 'pending':
                return $query->whereNull('r.approvalstatus')
                    ->where('app.jumlahapproval', '>', 0);
            case 'declined':
                return $query->where('r.approvalstatus', '0');
            default:
                return $query;
        }
    }

    private function applyStatusFilter($query, string $filter)
    {
        switch ($filter) {
            case 'completed':
                return $query->where('r.status', 'Completed');
            case 'in_progress':
                return $query->where('r.status', '!=', 'Completed');
            default:
                return $query;
        }
    }
}
