<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisTenagaKerja extends Model
{
    protected $table = 'jenistenagakerja';
    protected $primaryKey = 'idjenistenagakerja';
    public $timestamps = false;

    protected $fillable = [
        'idjenistenagakerja',
        'nama'
    ];

    /**
     * Relasi ke Activity (reverse)
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'jenistenagakerja', 'idjenistenagakerja');
    }
}