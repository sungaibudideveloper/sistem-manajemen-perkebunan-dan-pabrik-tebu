<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * LkhRepository
 * 
 * Handles all database queries related to LKH (Laporan Kegiatan Harian)
 * Uses surrogate ID for all joins
 */
class LkhRepository
{
        /**
     * Get LKH list for specific RKH
     */
    public function getLkhByRkhNo(string $companycode, string $rkhno): Collection
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->where('h.rkhno', $rkhno)
            ->select([
                'h.id',
                'h.lkhno',
                'h.activitycode',
                'a.activityname',
                'h.jenistenagakerja',
                'h.lkhdate',
                'h.totalworkers',
                'h.totalhasil',
                'h.totalsisa',
                'h.totalupahall',
                'h.createdat',
                'h.status',
                'h.issubmit',
                'h.submitby',
                'h.submitat',
                'h.jumlahapproval',
                'h.approval1flag',
                'h.approval2flag',
                'h.approval3flag',
                'app.jumlahapproval as required_approvals'
            ])
            ->orderBy('h.lkhno')
            ->get();
    }

    /**
     * Get LKH header by surrogate ID
     */
    public function findById(int $id): ?object
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                     ->on('h.companycode', '=', 'app.companycode');
            })
            ->where('h.id', $id)
            ->select([
                'h.*',
                'm.name as mandornama',
                'a.activityname',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3'
            ])
            ->first();
    }

    /**
     * Get LKH header by business key
     */
    public function findByLkhNo(string $companycode, string $lkhno): ?object
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandornama',
                'a.activityname',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3'
            ])
            ->first();
    }

    /**
     * ✅ FIXED: Get LKH plot details - JOIN menggunakan surrogate ID
     */
    public function getPlotDetails(string $companycode, string $lkhno): Collection
    {
        return DB::table('lkhdetailplot as ldp')
            // ✅ FIXED: Use batchid instead of batchno
            ->leftJoin('batch as b', 'ldp.batchid', '=', 'b.id')
            ->where('ldp.companycode', $companycode)
            ->where('ldp.lkhno', $lkhno)
            ->select([
                'ldp.*',
                'b.batchno',
                'b.lifecyclestatus',
                'b.batcharea',
                'b.tanggalpanen'
            ])
            ->orderBy('ldp.blok')
            ->orderBy('ldp.plot')
            ->get();
    }

    /**
     * Get LKH worker details
     */
    public function getWorkerDetails(string $companycode, string $lkhno): Collection
    {
        return DB::table('lkhdetailworker as ldw')
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('ldw.tenagakerjaid', '=', 'tk.tenagakerjaid')
                     ->where('tk.companycode', '=', $companycode);
            })
            ->where('ldw.companycode', $companycode)
            ->where('ldw.lkhno', $lkhno)
            ->select([
                'ldw.*',
                'tk.nama as tenagakerja_nama',
                'tk.nik',
                'tk.gender',
                'tk.jenistenagakerja'
            ])
            ->orderBy('ldw.tenagakerjaurutan')
            ->get();
    }

    /**
     * Get LKH material details
     */
    public function getMaterialDetails(string $companycode, string $lkhno): Collection
    {
        return DB::table('lkhdetailmaterial as ldm')
            ->leftJoin('herbisida as h', function($join) use ($companycode) {
                $join->on('ldm.itemcode', '=', 'h.itemcode')
                     ->where('h.companycode', '=', $companycode);
            })
            ->where('ldm.companycode', $companycode)
            ->where('ldm.lkhno', $lkhno)
            ->select([
                'ldm.*',
                'h.itemname',
                'h.measure',
                'h.jenis'
            ])
            ->orderBy('ldm.plot')
            ->orderBy('ldm.itemcode')
            ->get();
    }

    /**
     * ✅ FIXED: Get LKH kendaraan details - JOIN menggunakan surrogate ID
     */
    public function getKendaraanDetails(string $companycode, string $lkhno): Collection
    {
        return DB::table('lkhdetailkendaraan as ldk')
            // ✅ FIXED: Use kendaraanid instead of nokendaraan
            ->leftJoin('kendaraan as k', 'ldk.kendaraanid', '=', 'k.id')
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('ldk.operatorid', '=', 'tk.tenagakerjaid')
                     ->where('tk.companycode', '=', $companycode);
            })
            ->where('ldk.companycode', $companycode)
            ->where('ldk.lkhno', $lkhno)
            ->select([
                'ldk.*',
                'k.nokendaraan',
                'k.jenis as kendaraan_jenis',
                'tk.nama as operator_nama',
                'tk.nik as operator_nik'
            ])
            ->get();
    }

    /**
     * ✅ FIXED: Get LKH BSM details - JOIN menggunakan surrogate ID
     */
    public function getBsmDetails(string $companycode, string $lkhno): Collection
    {
        return DB::table('lkhdetailbsm as ldb')
            // ✅ FIXED: Use batchid instead of batchno
            ->leftJoin('batch as b', 'ldb.batchid', '=', 'b.id')
            ->where('ldb.companycode', $companycode)
            ->where('ldb.lkhno', $lkhno)
            ->select([
                'ldb.*',
                'b.batchno',
                'b.lifecyclestatus'
            ])
            ->orderBy('ldb.plot')
            ->get();
    }

    /**
     * Get LKH panen details with batch calculations (STC, HC, BC)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return Collection
     */
    public function getPanenDetails(string $companycode, string $lkhno): Collection
    {
        $lkhDate = $this->getLkhDate($companycode, $lkhno);
        
        return DB::table('lkhdetailplot as ldp')
            ->leftJoin('batch as b', function($join) use ($companycode) {
                $join->on('ldp.batchid', '=', 'b.id')
                     ->where('b.companycode', '=', $companycode);
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.lkhno', $lkhno)
            ->select([
                'ldp.plot',
                'ldp.blok',
                'ldp.luasrkh',
                'ldp.luashasil',
                'ldp.createdat',
                'ldp.fieldbalancerit',
                'ldp.fieldbalanceton',
                'b.batchno',
                'b.batcharea',
                'b.tanggalpanen',
                'b.lifecyclestatus as kodestatus',
                
                // STC calculation (Standing To Cut)
                DB::raw("(
                    COALESCE(b.batcharea, 0) - 
                    COALESCE((
                        SELECT SUM(ldp2.luashasil)
                        FROM lkhdetailplot ldp2
                        JOIN lkhhdr lh2 ON ldp2.lkhhdrid = lh2.id
                        WHERE ldp2.companycode = ldp.companycode
                        AND ldp2.batchid = ldp.batchid
                        AND lh2.approvalstatus = '1'
                        AND lh2.lkhdate < '{$lkhDate}'
                    ), 0)
                ) as stc"),
                
                DB::raw('COALESCE(ldp.luashasil, 0) as hc'),
                
                // BC calculation (Balance to Cut)
                DB::raw("(
                    (
                        COALESCE(b.batcharea, 0) - 
                        COALESCE((
                            SELECT SUM(ldp2.luashasil)
                            FROM lkhdetailplot ldp2
                            JOIN lkhhdr lh2 ON ldp2.lkhhdrid = lh2.id
                            WHERE ldp2.companycode = ldp.companycode
                            AND ldp2.batchid = ldp.batchid
                            AND lh2.approvalstatus = '1'
                            AND lh2.lkhdate < '{$lkhDate}'
                        ), 0)
                    ) - COALESCE(ldp.luashasil, 0)
                ) as bc"),
                
                // Hari Tebang
                DB::raw("CASE 
                    WHEN b.tanggalpanen IS NULL THEN 1
                    ELSE DATEDIFF('{$lkhDate}', b.tanggalpanen) + 1
                END as haritebang")
            ])
            ->orderBy('ldp.blok')
            ->orderBy('ldp.plot')
            ->get();
    }

    /**
     * Get kontraktor summary for LKH Panen
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return Collection
     */
    public function getKontraktorSummary(string $companycode, string $lkhno): Collection
    {
        return DB::table('suratjalanpos as sjp')
            ->leftJoin('kontraktor as k', function($join) use ($companycode) {
                $join->on('sjp.namakontraktor', '=', 'k.id')
                     ->where('k.companycode', '=', $companycode);
            })
            ->where('sjp.companycode', $companycode)
            ->where('sjp.suratjalanno', 'LIKE', "%-{$lkhno}-%")
            ->select([
                'sjp.namakontraktor as kontraktor_id',
                'k.namakontraktor as kontraktor_nama',
                DB::raw('COUNT(DISTINCT sjp.namasubkontraktor) as total_subkontraktor'),
                DB::raw('COUNT(DISTINCT sjp.plot) as total_plot'),
                DB::raw("GROUP_CONCAT(DISTINCT sjp.plot ORDER BY sjp.plot SEPARATOR ', ') as list_plot")
            ])
            ->groupBy('sjp.namakontraktor', 'k.namakontraktor')
            ->orderBy('k.namakontraktor')
            ->get();
    }

    /**
     * Get subkontraktor detail per plot for LKH Panen
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return Collection
     */
    public function getSubkontraktorDetail(string $companycode, string $lkhno): Collection
    {
        return DB::table('suratjalanpos as sjp')
            ->join('lkhdetailplot as ldp', function($join) use ($companycode, $lkhno) {
                $join->on('sjp.plot', '=', 'ldp.plot')
                     ->where('ldp.companycode', '=', $companycode)
                     ->where('ldp.lkhno', '=', $lkhno);
            })
            ->leftJoin('kontraktor as k', function($join) use ($companycode) {
                $join->on('sjp.namakontraktor', '=', 'k.id')
                     ->where('k.companycode', '=', $companycode);
            })
            ->leftJoin('subkontraktor as sk', function($join) use ($companycode) {
                $join->on('sjp.namasubkontraktor', '=', 'sk.id')
                     ->where('sk.companycode', '=', $companycode);
            })
            ->where('sjp.companycode', $companycode)
            ->where('sjp.suratjalanno', 'LIKE', "%-{$lkhno}-%")
            ->select([
                'ldp.blok',
                'sjp.plot',
                'sjp.namakontraktor as kontraktor_id',
                'k.namakontraktor as kontraktor_nama',
                'sjp.namasubkontraktor as subkontraktor_id',
                'sk.namasubkontraktor as subkontraktor_nama',
                DB::raw('COUNT(sjp.suratjalanno) as jumlah_sj')
            ])
            ->groupBy(
                'ldp.blok',
                'sjp.plot',
                'sjp.namakontraktor',
                'k.namakontraktor',
                'sjp.namasubkontraktor',
                'sk.namasubkontraktor'
            )
            ->orderBy('ldp.blok')
            ->orderBy('sjp.plot')
            ->get();
    }

    /**
     * Get ongoing plots for mandor (panen in progress, not in current LKH)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param string $mandorid
     * @return Collection
     */
    public function getOngoingPlotsForMandor(string $companycode, string $lkhno, string $mandorid): Collection
    {
        $lkhDate = $this->getLkhDate($companycode, $lkhno);
        
        // Get plots in current LKH (to exclude)
        $currentLkhPlots = DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->pluck('plot')
            ->toArray();
        
        return DB::table('masterlist as m')
            ->join('batch as b', function($join) use ($companycode) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                     ->where('b.companycode', '=', $companycode);
            })
            ->leftJoin(DB::raw('(
                SELECT 
                    ldp.plot,
                    b2.batchno,
                    SUM(ldp.luashasil) as total_dipanen,
                    MAX(lh.lkhdate) as last_harvest_date
                FROM lkhdetailplot ldp
                JOIN lkhhdr lh ON ldp.lkhhdrid = lh.id
                JOIN batch b2 ON ldp.batchid = b2.id
                WHERE ldp.companycode = "' . $companycode . '"
                    AND lh.mandorid = "' . $mandorid . '"
                    AND lh.approvalstatus = "1"
                GROUP BY ldp.plot, b2.batchno
            ) as harvest_summary'), function($join) {
                $join->on('m.plot', '=', 'harvest_summary.plot')
                     ->on('b.batchno', '=', 'harvest_summary.batchno');
            })
            ->where('m.companycode', $companycode)
            ->where('m.isactive', 1)
            ->where('b.isactive', 1)
            ->whereNotNull('b.tanggalpanen')
            ->whereNotNull('harvest_summary.total_dipanen')
            ->whereNotIn('m.plot', $currentLkhPlots)
            ->select([
                'm.plot',
                'm.blok',
                'b.batchno',
                'b.batcharea',
                'b.tanggalpanen',
                'b.lifecyclestatus as kodestatus',
                DB::raw('COALESCE(harvest_summary.total_dipanen, 0) as total_dipanen'),
                DB::raw('(b.batcharea - COALESCE(harvest_summary.total_dipanen, 0)) as sisa'),
                'harvest_summary.last_harvest_date',
                DB::raw('DATEDIFF("' . $lkhDate . '", harvest_summary.last_harvest_date) as days_since_harvest')
            ])
            ->havingRaw('sisa > 0')
            ->orderBy('m.blok')
            ->orderBy('m.plot')
            ->get();
    }

    /**
     * Get surat jalan list for plot and subkontraktor
     * 
     * @param string $companycode
     * @param string $plot
     * @param string $subkontraktorId
     * @param string $lkhno
     * @return Collection
     */
    public function getSuratJalanByPlot(string $companycode, string $plot, string $subkontraktorId, string $lkhno): Collection
    {
        $lkhDate = $this->getLkhDate($companycode, $lkhno);
        
        return DB::table('suratjalanpos as sj')
            ->leftJoin('timbanganpayload as tp', function($join) use ($companycode) {
                $join->on('sj.companycode', '=', 'tp.companycode')
                     ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
            })
            ->leftJoin('subkontraktor as sk', function($join) use ($companycode) {
                $join->on('sj.namasubkontraktor', '=', 'sk.id')
                     ->where('sk.companycode', '=', $companycode);
            })
            ->where('sj.companycode', $companycode)
            ->where('sj.plot', $plot)
            ->where('sj.namasubkontraktor', $subkontraktorId)
            ->whereDate('sj.tanggalcetakpossecurity', $lkhDate)
            ->select([
                'sj.suratjalanno',
                'sj.tanggalcetakpossecurity',
                'sk.namasubkontraktor',
                DB::raw('CASE WHEN tp.suratjalanno IS NULL THEN "Pending" ELSE "Sudah Timbang" END as status')
            ])
            ->orderBy('sj.tanggalcetakpossecurity', 'desc')
            ->get();
    }

    /**
     * Create LKH header
     * 
     * @param array $data
     * @return int (inserted ID)
     */
    public function create(array $data): int
    {
        return DB::table('lkhhdr')->insertGetId($data);
    }

    /**
     * Update LKH header by ID
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return DB::table('lkhhdr')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Update LKH header by lkhno
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param array $data
     * @return bool
     */
    public function updateByLkhNo(string $companycode, string $lkhno, array $data): bool
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->update($data);
    }

    /**
     * Delete LKH by ID (cascade handled by foreign keys)
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return DB::table('lkhhdr')
            ->where('id', $id)
            ->delete();
    }

    /**
     * Create LKH plot details
     * 
     * @param array $plots
     * @return bool
     */
    public function createPlotDetails(array $plots): bool
    {
        if (empty($plots)) {
            return true;
        }

        // Validate surrogate IDs
        foreach ($plots as $plot) {
            if (isset($plot['batchno']) && !isset($plot['batchid'])) {
                \Log::warning('Creating lkhdetailplot without batchid', $plot);
            }
            if (!isset($plot['lkhhdrid'])) {
                \Log::warning('Creating lkhdetailplot without lkhhdrid', $plot);
            }
        }

        return DB::table('lkhdetailplot')->insert($plots);
    }

    /**
     * Delete LKH plot details by lkhno
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return int
     */
    public function deletePlotDetails(string $companycode, string $lkhno): int
    {
        return DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->delete();
    }

    /**
     * Create LKH worker details
     * 
     * @param array $workers
     * @return bool
     */
    public function createWorkerDetails(array $workers): bool
    {
        if (empty($workers)) {
            return true;
        }

        // Validate lkhhdrid
        foreach ($workers as $worker) {
            if (!isset($worker['lkhhdrid'])) {
                \Log::warning('Creating lkhdetailworker without lkhhdrid', $worker);
            }
        }

        return DB::table('lkhdetailworker')->insert($workers);
    }

    /**
     * Delete LKH worker details by lkhno
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return int
     */
    public function deleteWorkerDetails(string $companycode, string $lkhno): int
    {
        return DB::table('lkhdetailworker')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->delete();
    }

    /**
     * Create LKH material details
     * 
     * @param array $materials
     * @return bool
     */
    public function createMaterialDetails(array $materials): bool
    {
        if (empty($materials)) {
            return true;
        }

        // Validate lkhhdrid
        foreach ($materials as $material) {
            if (!isset($material['lkhhdrid'])) {
                \Log::warning('Creating lkhdetailmaterial without lkhhdrid', $material);
            }
        }

        return DB::table('lkhdetailmaterial')->insert($materials);
    }

    public function createKendaraanDetails(array $kendaraan): bool
    {
        if (empty($kendaraan)) {
            return true;
        }

        // Validate surrogate IDs
        foreach ($kendaraan as $k) {
            if (isset($k['nokendaraan']) && !isset($k['kendaraanid'])) {
                \Log::warning('Creating lkhdetailkendaraan without kendaraanid', $k);
            }
            if (!isset($k['lkhhdrid'])) {
                \Log::warning('Creating lkhdetailkendaraan without lkhhdrid', $k);
            }
        }

        return DB::table('lkhdetailkendaraan')->insert($kendaraan);
    }

    /**
     * Delete kendaraan details
     */
    public function deleteKendaraanDetails(string $companycode, string $lkhno): int
    {
        return DB::table('lkhdetailkendaraan')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->delete();
    }

    /**
     * Delete LKH material details by lkhno
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return int
     */
    public function deleteMaterialDetails(string $companycode, string $lkhno): int
    {
        return DB::table('lkhdetailmaterial')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->delete();
    }

    /**
     * Get LKH numbers for specific date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getLkhNumbersByDate(string $companycode, string $date): array
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('lkhdate', $date)
            ->pluck('lkhno')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Check if LKH exists
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return bool
     */
    public function exists(string $companycode, string $lkhno): bool
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
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
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->where('h.issubmit', 1)
            ->where(function($query) use ($jabatanId) {
                $query->where(function($q) use ($jabatanId) {
                    // Level 1 approval
                    $q->where('h.approval1idjabatan', $jabatanId)
                      ->whereNull('h.approval1flag');
                })->orWhere(function($q) use ($jabatanId) {
                    // Level 2 approval
                    $q->where('h.approval2idjabatan', $jabatanId)
                      ->where('h.approval1flag', '1')
                      ->whereNull('h.approval2flag');
                })->orWhere(function($q) use ($jabatanId) {
                    // Level 3 approval
                    $q->where('h.approval3idjabatan', $jabatanId)
                      ->where('h.approval1flag', '1')
                      ->where('h.approval2flag', '1')
                      ->whereNull('h.approval3flag');
                });
            })
            ->select([
                'h.*',
                'm.name as mandor_nama',
                'a.activityname',
                DB::raw('CASE 
                    WHEN h.approval1idjabatan = '.$jabatanId.' AND h.approval1flag IS NULL THEN 1
                    WHEN h.approval2idjabatan = '.$jabatanId.' AND h.approval1flag = "1" AND h.approval2flag IS NULL THEN 2
                    WHEN h.approval3idjabatan = '.$jabatanId.' AND h.approval1flag = "1" AND h.approval2flag = "1" AND h.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('h.lkhdate', 'desc')
            ->get();
    }

    /**
     * Get LKH approval detail (with approval history)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return object|null
     */
    public function getApprovalDetail(string $companycode, string $lkhno): ?object
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('user as u1', 'h.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'h.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'h.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'h.approval1idjabatan', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'h.approval2idjabatan', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'h.approval3idjabatan', '=', 'j3.idjabatan')
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandor_nama',
                'a.activityname',
                'h.jumlahapproval',
                'h.approval1idjabatan',
                'h.approval2idjabatan', 
                'h.approval3idjabatan',
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
     * Get plot info for activity (luas, sisa, batch)
     * 
     * @param string $companycode
     * @param string $plot
     * @param string $activitycode
     * @return object|null
     */
    public function getPlotInfo(string $companycode, string $plot, string $activitycode): ?object
    {
        $plotData = DB::table('masterlist as m')
            ->leftJoin('batch as b', function ($join) use ($companycode) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                     ->where('b.companycode', '=', $companycode)
                     ->where('b.isactive', '=', 1);
            })
            ->where('m.companycode', $companycode)
            ->where('m.plot', $plot)
            ->where('m.isactive', 1)
            ->select([
                'm.plot',
                'm.blok',
                'm.activebatchno',
                'b.id as batchid',
                'b.batchno',
                'b.batcharea',
                'b.lifecyclestatus',
                'b.tanggalpanen',
            ])
            ->first();

        if (!$plotData) {
            return null;
        }

        // Calculate total sudah dikerjakan
        $totalSudahDikerjakan = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhhdrid', '=', 'lh.id');
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.plot', $plot)
            ->where('lh.activitycode', $activitycode)
            ->where('lh.approvalstatus', '1')
            ->sum('ldp.luashasil');

        $plotData->totalsudahdikerjakan = (float) $totalSudahDikerjakan;
        $plotData->luassisa = (float) $plotData->batcharea - (float) $totalSudahDikerjakan;

        return $plotData;
    }

    /**
     * ✅ FIXED: Create BSM details - Pastikan isi batchid & lkhhdrid
     */
    public function createBsmDetails(array $bsm): bool
    {
        if (empty($bsm)) {
            return true;
        }

        // Validate surrogate IDs
        foreach ($bsm as $b) {
            if (isset($b['batchno']) && !isset($b['batchid'])) {
                \Log::warning('Creating lkhdetailbsm without batchid', $b);
            }
            if (!isset($b['lkhhdrid'])) {
                \Log::warning('Creating lkhdetailbsm without lkhhdrid', $b);
            }
        }

        return DB::table('lkhdetailbsm')->insert($bsm);
    }

    /**
     * Delete BSM details
     */
    public function deleteBsmDetails(string $companycode, string $lkhno): int
    {
        return DB::table('lkhdetailbsm')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->delete();
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Get LKH date (helper for subqueries)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return string
     */
    private function getLkhDate(string $companycode, string $lkhno): string
    {
        $date = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->value('lkhdate');
        
        return $date ?? now()->format('Y-m-d');
    }
    
    /**
     * Get last LKH number for RKH (for sequence generation)
     */
    public function getLastLkhNoForRkh(string $companycode, string $rkhno): ?object
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->lockForUpdate()
            ->orderBy(DB::raw('CAST(SUBSTRING(lkhno, 12, 2) AS UNSIGNED)'), 'desc')
            ->first();
    }

    /**
     * Get total upah for LKH
     */
    public function getTotalUpah(string $companycode, string $lkhno): float
    {
        return DB::table('lkhdetailworker')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->sum('totalupah') ?? 0;
    }

    /**
     * Get total luas hasil for LKH
     */
    public function getTotalLuasHasil(string $companycode, string $lkhno): float
    {
        return DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->sum('luashasil') ?? 0;
    }
}