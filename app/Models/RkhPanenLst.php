<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RkhPanenLst extends Model
{
    protected $table = 'rkhpanenlst';
    public $timestamps = false;
    
    // ✅ HAPUS composite primary key, biarkan default (id)
    // Atau set ke null jika tidak ada id column
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'companycode',
        'rkhpanenno',
        'kontraktorid',
        'jenispanen',
        'rencananetto',
        'rencanaha',
        'estimasiyph',
        'tenagatebangjumlah',
        'tenagamuatjumlah',
        'armadawl',
        'armadaumum',
        'mesinpanen',
        'grabloader',
        'lokasiplot',
    ];
}