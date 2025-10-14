<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JabatanPermission extends Model
{
    protected $table = 'jabatanpermissions';
    
    // Composite primary key
    protected $primaryKey = ['idjabatan', 'permissionid'];
    public $incrementing = false;
    protected $keyType = 'array';
    
    // Custom timestamps
    public $timestamps = true;
    const CREATED_AT = 'createdat';
    const UPDATED_AT = null; // Table tidak punya kolom updatedat
    
    protected $fillable = [
        'idjabatan',
        'permissionid', 
        'isactive',
        'grantedby',
        'createdat'
    ];
    
    protected $casts = [
        'isactive' => 'boolean',
        'createdat' => 'datetime'
    ];
    
    // Override getKeyName untuk composite key
    public function getKeyName()
    {
        return $this->primaryKey;
    }
    
    // Override setKeysForSaveQuery untuk composite key
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }
    
    // Override getKeyForSaveQuery untuk composite key
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'idjabatan');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permissionid');
    }
}