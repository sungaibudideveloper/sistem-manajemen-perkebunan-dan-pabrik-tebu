<?php
// app\Models\RKHHdr.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Rkhhdr extends Model
{
    protected $table = 'rkhhdr';

    protected $primaryKey = 'rkhno';
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'companycode',
        'rkhno',
        'rkhdate',
        'manpower',
        'totalluas',
        'mandorid',
        'activitygroup',
        'keterangan',
        'jumlahapproval',
        'approval1idjabatan',
        'approval1userid',
        'approval1date',
        'approval1flag',
        'approval2idjabatan',
        'approval2userid',
        'approval2date',
        'approval2flag',
        'approval3idjabatan',
        'approval3userid',
        'approval3date',
        'approval3flag',
        'status',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
    ];

    protected $casts = [
        'rkhdate'            => 'date',
        'manpower'           => 'integer',
        'totalluas'          => 'float',
        'jumlahapproval'     => 'integer',
        'approval1idjabatan' => 'integer',
        'approval1date'      => 'datetime',
        'approval2idjabatan' => 'integer',
        'approval2date'      => 'datetime',
        'approval3idjabatan' => 'integer',
        'approval3date'      => 'datetime',
        'createdat'          => 'datetime',
        'updatedat'          => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // TAMBAHAN: Relasi ke ActivityGroup
    public function activityGroup()
    {
        return $this->belongsTo(ActivityGroup::class, 'activitygroup', 'activitygroup');
    }

    // TAMBAHAN: Relasi ke Approval berdasarkan activitygroup
    public function approvalSetting()
    {
        return $this->belongsTo(Approval::class, 'activitygroup', 'category')
                    ->where('companycode', $this->companycode);
    }

    // TAMBAHAN: Method untuk mendapatkan approval yang diperlukan
    public function getRequiredApprovals()
    {
        $approval = $this->approvalSetting;
        if (!$approval) return [];

        $required = [];
        
        if ($approval->idjabatanapproval1) {
            $required[] = [
                'level' => 1,
                'jabatan_id' => $approval->idjabatanapproval1,
                'status' => $this->approval1flag,
                'date' => $this->approval1date,
                'user_id' => $this->approval1userid
            ];
        }
        
        if ($approval->idjabatanapproval2) {
            $required[] = [
                'level' => 2,
                'jabatan_id' => $approval->idjabatanapproval2,
                'status' => $this->approval2flag,
                'date' => $this->approval2date,
                'user_id' => $this->approval2userid
            ];
        }
        
        if ($approval->idjabatanapproval3) {
            $required[] = [
                'level' => 3,
                'jabatan_id' => $approval->approvali3djabatan,
                'status' => $this->approval3flag,
                'date' => $this->approval3date,
                'user_id' => $this->approval3userid
            ];
        }

        return $required;
    }

    // Method untuk check apakah sudah fully approved
    public function isFullyApproved()
    {
        $required = $this->getRequiredApprovals();
        if (empty($required)) return true;

        foreach ($required as $approval) {
            if ($approval['status'] !== '1') {
                return false;
            }
        }
        
        return true;
    }

    public function getDataBsmSJ($companycode, $tanggalawal, $tanggalakhir)
    {
        $query = "
            SELECT 
                r.companycode,
                r.rkhno,
                r.rkhdate,
                r.mandorid,
                r.activitygroup,
                r.totalluas,
                r.manpower,
                
                -- Count jumlah LKH yang terkait dengan RKH ini
                COUNT(DISTINCT l.lkhno) AS jumlah_lkh,
                
                -- Count jumlah BSM detail yang terkait dengan LKH dari RKH ini
                COUNT(DISTINCT b.id) AS jumlah_bsm,
                
                -- Additional info untuk keperluan display
                GROUP_CONCAT(DISTINCT l.lkhno ORDER BY l.lkhdate SEPARATOR ', ') AS list_lkhno,
                MIN(l.lkhdate) AS lkh_date_start,
                MAX(l.lkhdate) AS lkh_date_end,
                
                -- BSM averages
                AVG(b.nilaibersih) AS avg_bersih,
                AVG(b.nilaisegar) AS avg_segar,
                AVG(b.nilaimanis) AS avg_manis,
                AVG(b.averagescore) AS avg_bsm_score,
                
                -- Grade counts
                SUM(CASE WHEN b.grade = 'A' THEN 1 ELSE 0 END) AS bsm_grade_a,
                SUM(CASE WHEN b.grade = 'B' THEN 1 ELSE 0 END) AS bsm_grade_b,
                SUM(CASE WHEN b.grade = 'C' THEN 1 ELSE 0 END) AS bsm_grade_c,
                
                -- Metadata
                r.inputby AS created_by,
                r.createdat AS created_at

            FROM rkhhdr r
            LEFT JOIN lkhhdr l ON r.companycode = l.companycode AND r.rkhno = l.rkhno
            LEFT JOIN lkhdetailbsm b ON l.companycode = b.companycode AND l.lkhno = b.lkhno

            WHERE 
                r.companycode = ?
                AND r.rkhdate BETWEEN ? AND ?
                and l.activitycode = '4.7'

            GROUP BY 
                r.companycode,
                r.rkhno,
                r.rkhdate,
                r.mandorid,
                r.activitygroup,
                r.totalluas,
                r.manpower,
                r.inputby,
                r.createdat

            HAVING 
                -- Filter hanya RKH yang memiliki LKH atau BSM data
                (COUNT(DISTINCT l.lkhno) > 0 OR COUNT(DISTINCT b.id) > 0)

            ORDER BY 
                r.rkhdate DESC, 
                r.rkhno ASC
        ";

        try {
            $results = DB::select($query, [$companycode, $tanggalawal, $tanggalakhir]);
            
            // Convert to collection and transform data
            return collect($results)->map(function ($item) {
                return (object) [
                    // Main display data
                    'rkhno' => $item->rkhno,
                    'rkhdate' => $item->rkhdate,
                    'tanggal' => $item->rkhdate, // alias
                    'jumlah_lkh' => (int) $item->jumlah_lkh,
                    'jumlah_bsm' => (int) $item->jumlah_bsm,
                    
                    // RKH details
                    'companycode' => $item->companycode,
                    'mandorid' => $item->mandorid,
                    'activitygroup' => $item->activitygroup,
                    'totalluas' => (float) $item->totalluas,
                    'manpower' => (int) $item->manpower,
                    
                    // LKH details
                    'list_lkhno' => $item->list_lkhno,
                    'lkh_date_start' => $item->lkh_date_start,
                    'lkh_date_end' => $item->lkh_date_end,
                    
                    // BSM details
                    'avg_bersih' => $item->avg_bersih ? round((float) $item->avg_bersih, 2) : 0,
                    'avg_segar' => $item->avg_segar ? round((float) $item->avg_segar, 2) : 0,
                    'avg_manis' => $item->avg_manis ? round((float) $item->avg_manis, 2) : 0,
                    'avg_bsm_score' => $item->avg_bsm_score ? round((float) $item->avg_bsm_score, 2) : 0,
                    'bsm_grade_a' => (int) $item->bsm_grade_a,
                    'bsm_grade_b' => (int) $item->bsm_grade_b,
                    'bsm_grade_c' => (int) $item->bsm_grade_c,
                    
                    // Metadata
                    'created_by' => $item->created_by,
                    'created_at' => $item->created_at,
                    'keterangan' => "RKH dengan {$item->jumlah_lkh} LKH dan {$item->jumlah_bsm} BSM data"
                ];
            });

        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error in getDataBsmSJ (RKH Summary): ' . $e->getMessage());
            
            // Return empty collection on error
            return collect([]);
        }
    }

    // Jika Anda belum memiliki method ini, tambahkan juga untuk keperluan transformasi status
    /**
     * Convert status to human readable format (opsional)
     * 
     * @param string $status
     * @return string
     */
    private function getStatusDisplay($status)
    {
        $statusMap = [
            'EMPTY' => 'Kosong',
            'DRAFT' => 'Draft',
            'COMPLETED' => 'Selesai',
            'SUBMITTED' => 'Diajukan',
            'APPROVED' => 'Disetujui',
            'REJECTED' => 'Ditolak'
        ];

        return $statusMap[$status] ?? $status;
    }

    public function getBsmDetailByRkh($companycode, $rkhno)
    {
        $query = "
            SELECT 
                a.companycode, 
                a.suratjalanno, 
                c.createdat,
                a.plot, 
                c.id, 
                c.grade, 
                a.nilaibersih, 
                a.nilaisegar, 
                a.nilaimanis, 
                a.averagescore,
                b.lkhno,
                b.lkhdate,
                COALESCE(c.keterangan, '') as keterangan
            FROM suratjalanpos a
            LEFT JOIN lkhhdr b ON a.companycode = b.companycode AND b.rkhno = ?
            LEFT JOIN lkhdetailbsm c ON b.companycode = c.companycode 
                AND b.lkhno = c.lkhno 
                AND a.suratjalanno = c.suratjalanno
            WHERE LEFT(a.tanggalangkut, 10) = b.lkhdate 
                AND a.companycode = ?
                AND c.suratjalanno IS NOT NULL
            ORDER BY a.plot, a.suratjalanno
        ";
        
        try {
            $results = DB::select($query, [$rkhno, $companycode]);
            
            // Convert to collection and transform data
            return collect($results)->map(function ($item) {
                return (object) [
                    'suratjalanno' => $item->suratjalanno,
                    'nilaibersih' => (float) ($item->nilaibersih ?? 0),
                    'nilaisegar' => (float) ($item->nilaisegar ?? 0),
                    'nilaimanis' => (float) ($item->nilaimanis ?? 0),
                    'averagescore' => (float) ($item->averagescore ?? 0),
                    'grade' => $item->grade ?? '',
                    'lkhno' => $item->lkhno ?? '',
                    'lkhdate' => $item->lkhdate ?? '',
                    'plot' => $item->plot ?? '',
                    'keterangan' => $item->keterangan ?? '',
                    'createdat' => $item->createdat ?? ''
                ];
            });
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error in getBsmDetailByRkh: ' . $e->getMessage());
            
            // Return empty collection on error
            return collect([]);
        }
    }
}