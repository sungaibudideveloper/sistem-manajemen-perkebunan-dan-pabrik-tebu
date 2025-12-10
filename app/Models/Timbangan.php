<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Timbangan extends Model
{
    protected $table = 'timbanganpayload';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['nom', 'companycode', 'suratjalanno', 'tgl1', 'jam1', 'tgl2', 'jam2', 'nopol', 'jnsk',
    'supl', 'gsupl', 'area', 'item', 'note', 'ket1', 'ket2', 'ket3', 'donom', 'dotgl', 'bruto', 'brkend', 'raf', 'traf', 'netto',
    'flag', 'usr1', 'usr2'];

    public function getData($companycode, $idkontraktor, $startdate, $enddate){
        // Query dengan JOIN langsung ke lkhdetailbsm berdasarkan suratjalanno dan companycode
        $data = \DB::select("
            SELECT
                a.suratjalanno,
                b.tanggalangkut,
                c.kontraktorid,
                d.namakontraktor,
                b.namasupir,
                c.id,
                c.namasubkontraktor,
                b.nomorpolisi,
                b.plot,
                a.bruto,
                a.brkend,
                a.netto,
                a.traf,
                (a.netto - a.traf) AS beratbersih,
                b.muatgl,
                b.kodetebang,
                b.kendaraankontraktor,
                b.tebusulit,
                b.langsir,
                t.nettotrash AS trash_percentage,
                bsm.averagescore,
                bsm.grade
            FROM timbanganpayload a
            LEFT JOIN suratjalanpos b ON BINARY a.companycode = BINARY b.companycode
                AND BINARY a.suratjalanno = BINARY b.suratjalanno
            LEFT JOIN subkontraktor c ON BINARY c.id = BINARY b.namasubkontraktor
                AND BINARY c.companycode = BINARY b.companycode
            LEFT JOIN kontraktor d ON BINARY c.kontraktorid = BINARY d.id
                AND BINARY c.companycode = BINARY d.companycode
            LEFT JOIN trash t ON BINARY a.suratjalanno = BINARY t.suratjalanno
                AND BINARY a.companycode = BINARY t.companycode
            LEFT JOIN lkhdetailbsm bsm ON BINARY a.companycode = BINARY bsm.companycode
                AND BINARY a.suratjalanno = BINARY bsm.suratjalanno
            WHERE a.companycode = ?
                AND DATE(b.tanggalangkut) BETWEEN ? AND ?
                AND b.namakontraktor = ?
            ORDER BY b.tanggalangkut ASC, a.suratjalanno ASC
        ", [$companycode, $startdate, $enddate, $idkontraktor]);

        // Logika fallback trash (tetap sama seperti sebelumnya)
        $trashCache = [];

        // Pass pertama: kumpulkan trash yang ada per subkontraktor per tanggal
        foreach ($data as $row) {
            if (!empty($row->trash_percentage) && $row->trash_percentage > 0) {
                $tanggal = date('Y-m-d', strtotime($row->tanggalangkut));
                $key = $row->namasubkontraktor . '_' . $tanggal;
                $trashCache[$key] = $row->trash_percentage;
            }
        }

        // Pass kedua: terapkan fallback untuk yang tidak ada trash
        foreach ($data as $row) {
            if (empty($row->trash_percentage) || $row->trash_percentage == 0) {
                $tanggal = date('Y-m-d', strtotime($row->tanggalangkut));
                $key = $row->namasubkontraktor . '_' . $tanggal;
                $row->trash_percentage = $trashCache[$key] ?? 0;
            }
        }

        // TIDAK ADA LAGI FALLBACK BSM - karena sekarang based on suratjalanno
        // Jika tidak ada data BSM untuk suratjalanno tersebut, maka akan kosong

        return $data;
    }

    // Alternative method jika masih ada duplicate - menggunakan Collection untuk deduplicate
    public function getDataAlternative($companycode, $idkontraktor, $startdate, $enddate){
        $data = \DB::select("
            SELECT
                a.suratjalanno,
                b.tanggalangkut,
                c.kontraktorid,
                d.namakontraktor,
                b.namasupir,
                c.id,
                c.namasubkontraktor,
                b.nomorpolisi,
                b.plot,
                a.bruto,
                a.brkend,
                a.netto,
                a.traf,
                (a.netto - a.traf) AS beratbersih,
                b.muatgl,
                b.kodetebang,
                b.kendaraankontraktor,
                b.tebusulit,
                b.langsir,
                t.netto_trash AS trash_percentage,
                bsm.averagescore,
                bsm.grade
            FROM timbanganpayload a
            LEFT JOIN suratjalanpos b ON BINARY a.companycode = BINARY b.companycode
                AND BINARY a.suratjalanno = BINARY b.suratjalanno
            LEFT JOIN subkontraktor c ON BINARY c.id = BINARY b.namasubkontraktor
                AND BINARY c.companycode = BINARY b.companycode
            LEFT JOIN kontraktor d ON BINARY c.kontraktorid = BINARY d.id
                AND BINARY c.companycode = BINARY d.companycode
            LEFT JOIN trash t ON BINARY a.suratjalanno = BINARY t.suratjalanno
                AND BINARY a.companycode = BINARY t.companycode
            LEFT JOIN lkhdetailbsm bsm ON BINARY a.companycode = BINARY bsm.companycode
                AND BINARY a.suratjalanno = BINARY bsm.suratjalanno
            WHERE a.companycode = ?
                AND DATE(b.tanggalangkut) BETWEEN ? AND ?
                AND b.namakontraktor = ?
            ORDER BY b.tanggalangkut ASC, a.suratjalanno ASC
        ", [$companycode, $startdate, $enddate, $idkontraktor]);

        // Deduplicate berdasarkan suratjalanno menggunakan Collection
        $dataCollection = collect($data)->unique('suratjalanno')->values();

        // Convert back to array untuk consistency dengan method sebelumnya
        $data = $dataCollection->toArray();

        // Apply trash fallback logic (sama seperti sebelumnya)
        $trashCache = [];

        // Pass pertama: kumpulkan trash yang ada per subkontraktor per tanggal
        foreach ($data as $row) {
            if (!empty($row->trash_percentage) && $row->trash_percentage > 0) {
                $tanggal = date('Y-m-d', strtotime($row->tanggalangkut));
                $key = $row->namasubkontraktor . '_' . $tanggal;
                $trashCache[$key] = $row->trash_percentage;
            }
        }

        // Pass kedua: terapkan fallback untuk yang tidak ada trash
        foreach ($data as $row) {
            if (empty($row->trash_percentage) || $row->trash_percentage == 0) {
                $tanggal = date('Y-m-d', strtotime($row->tanggalangkut));
                $key = $row->namasubkontraktor . '_' . $tanggal;
                $row->trash_percentage = $trashCache[$key] ?? 0;
            }
        }

        return $data;
    }

    // Method untuk debug - melihat duplicate records
    public function checkDuplicates($companycode, $idkontraktor, $startdate, $enddate){
        $data = \DB::select("
            SELECT
                a.suratjalanno,
                COUNT(*) as duplicate_count
            FROM timbanganpayload a
            LEFT JOIN suratjalanpos b ON BINARY a.companycode = BINARY b.companycode
                AND BINARY a.suratjalanno = BINARY b.suratjalanno
            LEFT JOIN subkontraktor c ON BINARY c.id = BINARY b.namasubkontraktor
                AND BINARY c.companycode = BINARY b.companycode
            LEFT JOIN kontraktor d ON BINARY c.kontraktorid = BINARY d.id
                AND BINARY c.companycode = BINARY d.companycode
            LEFT JOIN trash t ON BINARY a.suratjalanno = BINARY t.suratjalanno
                AND BINARY a.companycode = BINARY t.companycode
            LEFT JOIN lkhdetailbsm bsm ON BINARY a.companycode = BINARY bsm.companycode
                AND BINARY a.suratjalanno = BINARY bsm.suratjalanno
            WHERE a.companycode = ?
                AND DATE(b.tanggalangkut) BETWEEN ? AND ?
                AND b.namakontraktor = ?
            GROUP BY a.suratjalanno
            HAVING COUNT(*) > 1
            ORDER BY duplicate_count DESC
        ", [$companycode, $startdate, $enddate, $idkontraktor]);

        return $data;
    }

    // Method tambahan untuk debug BSM data berdasarkan suratjalanno
    public function checkBsmBySuratJalan($companycode, $suratjalanno = null){
        $whereClause = "WHERE bsm.companycode = ?";
        $params = [$companycode];
        
        if ($suratjalanno) {
            $whereClause .= " AND bsm.suratjalanno = ?";
            $params[] = $suratjalanno;
        }
        
        $data = \DB::select("
            SELECT
                bsm.suratjalanno,
                bsm.plot,
                bsm.kodetebang,
                bsm.averagescore,
                bsm.grade,
                bsm.nilaibersih,
                bsm.nilaisegar,
                bsm.nilaimanis,
                bsm.keterangan
            FROM lkhdetailbsm bsm
            {$whereClause}
            ORDER BY bsm.suratjalanno ASC
        ", $params);

        return $data;
    }
}