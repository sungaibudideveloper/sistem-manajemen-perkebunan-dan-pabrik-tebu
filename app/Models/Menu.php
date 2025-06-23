<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu'; // Nama tabel menu
    public $timestamps = false;

    protected $primaryKey = 'menuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'menuid',
        'slug',
        'name',
        'updateby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    // Relasi ke Submenu - Menampilkan submenu yang menjadi parent (parentid = NULL)
    public function submenus()
    {
        return $this->hasMany(Submenu::class, 'menuid', 'menuid')
            ->whereNull('parentid'); // Hanya submenu dengan parentid NULL
    }

}
