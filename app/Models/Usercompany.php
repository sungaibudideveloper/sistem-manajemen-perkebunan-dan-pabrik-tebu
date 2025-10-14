<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserCompany extends Model
{
    protected $table = 'usercompany';
    
    // Composite primary key
    protected $primaryKey = ['userid', 'companycode'];
    public $incrementing = false;
    protected $keyType = 'array';
    
    // Timestamps
    public $timestamps = true;
    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';
    
    protected $fillable = [
        'userid',
        'companycode',
        'isactive',
        'grantedby',
        'createdat'
    ];
    
    protected $casts = [
        'isactive' => 'boolean'
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

    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }
    
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}