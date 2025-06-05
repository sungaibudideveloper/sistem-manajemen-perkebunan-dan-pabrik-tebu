<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Use_hdr extends Model
{
    protected $table = 'Use_hdr';
    public $timestamps = false;

    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'companycode','rkhno','useno','totalluas','flagstatus','inputby','creatdat','updateby','updatedat'
    ];



// public function selectuse($companycode, $type=0){
//     if($type == 0){
//         $sql='';
//     }else{ $sql='b.*,'; }
//     return \DB::select(
//       "
//         SELECT DISTINCT e.herbisidagroupname, ".$sql." a.* FROM use_hdr a 
//         LEFT JOIN use_lst b ON a.rkhno = b.rkhno
//         LEFT JOIN rkhhdr f ON a.rkhno = f.rkhno
//         LEFT JOIN rkhlst g ON a.rkhno = g.rkhno 
//         LEFT JOIN herbisida c ON a.companycode = c.companycode AND b.itemcode = c.itemcode
//         LEFT JOIN herbisidadosage d ON a.companycode = c.companycode AND b.itemcode = c.itemcode
//         LEFT JOIN herbisidagroup e ON a.companycode = c.companycode AND b.itemcode = c.itemcode AND g.herbisidagroupid = e.herbisidagroupid
//         WHERE a.companycode='".$companycode."'
//       "
//     );
//   }

public function selectuse($companycode, $rkhno = 0, $type = 0)
{ 
    return \DB::table('use_hdr as a')
        ->selectRaw('DISTINCT g.blok, g.plot, g.luasarea, f.mandorid, h.name AS mandorname, e.activitycode,e.herbisidagroupid, e.herbisidagroupname, a.*')
        ->leftJoin('use_lst as b', 'a.rkhno', '=', 'b.rkhno')
        ->leftJoin('rkhhdr as f', 'a.rkhno', '=', 'f.rkhno')
        ->leftJoin('rkhlst as g', 'a.rkhno', '=', 'g.rkhno')
        ->leftJoin('herbisida as c', function ($join) {
            $join->on('a.companycode', '=', 'c.companycode')
                ->on('b.itemcode', '=', 'c.itemcode');
        })
        ->leftJoin('herbisidadosage as d', function ($join) {
            $join->on('a.companycode', '=', 'd.companycode')  
                ->on('b.itemcode', '=', 'd.itemcode');        
        })
        ->leftJoin('herbisidagroup as e', function ($join) {
            $join->on('g.herbisidagroupid', '=', 'e.herbisidagroupid');
        })
        ->leftJoin('mandor as h', 'f.mandorid', '=', 'h.id')
        ->when($type == 1, function ($query) use ($rkhno) {
            $query->where('a.rkhno', $rkhno);
        }, function ($query) use ($companycode) {
            $query->where('a.companycode', $companycode);
        });
}
    


}