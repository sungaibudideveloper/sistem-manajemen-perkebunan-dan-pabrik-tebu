<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class usemateriallst extends Model
{
    protected $table = 'usemateriallst';
    public $timestamps = false;

    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'companycode', 'rkhno', 'itemcode', 'qty', 'qtyretur', 'unit', 'nouse', 'noretur'
    ];
    
  
public function joinlst($rkhno)
{ 
    return \DB::select(
    "
    SELECT u.itemcode, u.qty, u.qtyretur, u.unit, u.nouse, u.noretur, u.itemname, d.dosageperha, 
    d.dosageunit, d.herbisidagroupid FROM usemateriallst u
    JOIN HerbisidaDosage d ON u.itemcode = d.itemcode 
    AND u.herbisidagroupid = d.herbisidagroupid
    WHERE u.rkhno = '".$rkhno."'
    "
    );
}


}