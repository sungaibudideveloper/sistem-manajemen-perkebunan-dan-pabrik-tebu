<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Timbangan extends Model
{
    protected $table = 'timbangan_payload';
    public $timestamps = false;
    public $incrementing = false;
    
    protected $fillable = ['nom', 'companycode', 'suratjalanno', 'tgl1', 'jam1', 'tgl2', 'jam2', 'nopol', 'jnsk', 
    'supl', 'gsupl', 'area', 'item', 'note', 'ket1', 'ket2', 'ket3', 'donom', 'dotgl', 'bruto', 'brkend', 'raf', 'traf', 'netto', 
    'flag', 'usr1', 'usr2'];

    public function getData($companycode){
        // Query utama dengan penambahan join ke lkhhdr dan lkhdetailbsm
        $data = \DB::select("
            SELECT a.suratjalanno, b.tanggalangkut, c.kontraktorid, d.namakontraktor, b.namasupir, c.id, c.namasubkontraktor, b.nomorpolisi, b.plot, 
            a.bruto, a.brkend, a.netto, a.traf, SUM(a.netto - a.traf) AS beratbersih, b.muatgl, b.kodetebang, b.kendaraankontraktor, b.tebusulit, b.langsir,
            t.netto_trash AS trash_percentage,
            bsm.averagescore,
            bsm.grade
            FROM timbangan_payload a
            LEFT JOIN suratjalanpos b ON BINARY a.companycode = BINARY b.companycode 
                AND BINARY a.suratjalanno = BINARY b.suratjalanno
            LEFT JOIN subkontraktor c ON BINARY c.id = BINARY b.namasubkontraktor 
                AND BINARY c.companycode = BINARY b.companycode
            LEFT JOIN kontraktor d ON BINARY c.kontraktorid = BINARY d.id 
                AND BINARY c.companycode = BINARY d.companycode
            LEFT JOIN trash t ON BINARY a.suratjalanno = BINARY t.suratjalanno 
                AND BINARY a.companycode = BINARY t.companycode
            LEFT JOIN lkhhdr lkh ON BINARY b.companycode = BINARY lkh.companycode 
                AND DATE(b.tanggalangkut) = lkh.lkhdate
            LEFT JOIN lkhdetailbsm bsm ON BINARY lkh.companycode = BINARY bsm.companycode 
                AND BINARY lkh.lkhno = BINARY bsm.lkhno 
                AND BINARY b.plot = BINARY bsm.plot
            WHERE a.companycode = '".$companycode."'
            GROUP BY 
                a.suratjalanno, a.companycode, b.tanggalangkut, b.companycode, c.kontraktorid, 
                d.namakontraktor, b.namasupir, c.id, c.namasubkontraktor, b.nomorpolisi, 
                b.plot, a.bruto, a.brkend, a.netto, a.traf, b.muatgl, b.kodetebang, 
                b.kendaraankontraktor, b.tebusulit, b.langsir, t.netto_trash, bsm.averagescore, bsm.grade
            ORDER BY b.tanggalangkut asc
        ");

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

        // Logika fallback BSM: sama seperti trash, berdasarkan plot dan tanggal yang sama
        $bsmCache = [];
        
        // Pass pertama: kumpulkan BSM yang ada per plot per tanggal
        foreach ($data as $row) {
            if (!empty($row->averagescore) && $row->averagescore > 0) {
                $tanggal = date('Y-m-d', strtotime($row->tanggalangkut));
                $key = $row->plot . '_' . $tanggal;
                $bsmCache[$key] = [
                    'averagescore' => $row->averagescore,
                    'grade' => $row->grade
                ];
            }
        }
        
        // Pass kedua: terapkan fallback BSM untuk yang tidak ada data
        foreach ($data as $row) {
            if (empty($row->averagescore) || $row->averagescore == 0) {
                $tanggal = date('Y-m-d', strtotime($row->tanggalangkut));
                $key = $row->plot . '_' . $tanggal;
                if (isset($bsmCache[$key])) {
                    $row->averagescore = $bsmCache[$key]['averagescore'];
                    $row->grade = $bsmCache[$key]['grade'];
                }
            }
        }
        
        return $data;
    }
}