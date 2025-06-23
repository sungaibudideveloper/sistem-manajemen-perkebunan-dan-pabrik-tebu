<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submenu extends Model
{
    protected $table = 'submenu'; // Nama tabel submenu
    public $timestamps = false;

    protected $primaryKey = 'submenuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'submenuid',
        'menuid',
        'parentid',
        'name',
        'slug',
        'updatedby',
        'updatedat',
    ];

    protected $casts = [
        'updatedat' => 'datetime',
    ];

    // Relasi ke Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menuid', 'menuid');
    }

    // Relasi ke Parent Submenu
    public function parent()
    {
        return $this->belongsTo(Submenu::class, 'parentid', 'submenuid');
    }

    // Relasi ke Children Submenu
      public function children()
    {
        return $this->hasMany(Submenu::class, 'parentid', 'submenuid');
    }

    // TAMBAHAN: Relasi untuk memuat semua anak secara rekursif
    public function childrenRecursive()
    {
        // Ini akan memuat relasi 'children', dan untuk setiap anak, 
        // ia akan memuat relasi 'childrenRecursive' lagi.
        return $this->children()->with('childrenRecursive');
    }
}
