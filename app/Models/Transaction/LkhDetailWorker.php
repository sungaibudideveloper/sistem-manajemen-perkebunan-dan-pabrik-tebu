<?php
// =====================================================
// FILE: app/Models/Transaction/LkhDetailWorker.php
// =====================================================
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\TenagaKerja;

class LkhDetailWorker extends Model
{
    protected $table = 'lkhdetailworker';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'lkhno',
        'lkhhdrid',
        'tenagakerjaid',
        'tenagakerjaurutan',
        'jammasuk',
        'jamselesai',
        'totaljamkerja',
        'overtimehours',
        'premi',
        'upahharian',
        'upahperjam',
        'upahlembur',
        'upahborongan',
        'totalupah',
        'keterangan',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'tenagakerjaurutan' => 'integer',
        'totaljamkerja' => 'decimal:2',
        'overtimehours' => 'decimal:2',
        'premi' => 'decimal:2',
        'upahharian' => 'decimal:2',
        'upahperjam' => 'decimal:2',
        'upahlembur' => 'decimal:2',
        'upahborongan' => 'decimal:2',
        'totalupah' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    // Relationships (FK menggunakan surrogate ID)
    public function lkhHeader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhhdrid', 'id');
    }

    public function tenagaKerja()
    {
        return $this->belongsTo(TenagaKerja::class, 'tenagakerjaid', 'tenagakerjaid');
    }
}