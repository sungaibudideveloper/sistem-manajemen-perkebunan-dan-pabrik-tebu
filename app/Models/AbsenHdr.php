<?php
// app\Models\AbsenHdr.php - FIXED
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbsenHdr extends Model
{
    protected $table = 'absenhdr';
    
    // FIXED: Use single primary key instead of composite
    protected $primaryKey = 'absenno';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'absenno',
        'companycode',
        'mandorid',
        'totalpekerja',
        'status',
        'uploaddate',
        'approvaldate',
        'rejectdate',
        'updateBy',
    ];

    protected $casts = [
        'uploaddate' => 'datetime',
        'approvaldate' => 'datetime',
        'rejectdate' => 'datetime',
    ];

    // Relasi ke detail absen
    public function absenDetails()
    {
        return $this->hasMany(AbsenLst::class, 'absenno', 'absenno');
    }

    // Relasi ke mandor
    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorid', 'userid');
    }

    // Custom method to safely increment totalpekerja
    public function incrementTotalPekerja()
    {
        return DB::table('absenhdr')
            ->where('absenno', $this->absenno)
            ->where('companycode', $this->companycode)
            ->increment('totalpekerja');
    }

    // Custom method to safely update
    public function updateSafely($data)
    {
        return DB::table('absenhdr')
            ->where('absenno', $this->absenno)
            ->where('companycode', $this->companycode)
            ->update($data);
    }
}