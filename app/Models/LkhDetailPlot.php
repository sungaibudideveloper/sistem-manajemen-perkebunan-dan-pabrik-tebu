<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LkhDetailPlot extends Model
{
    protected $table = 'lkhdetailplot';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    
    protected $fillable = [
        'companycode',
        'lkhno',
        'blok',
        'plot',
        'luasrkh',
        'luashasil',
        'luassisa',
        'createdat',
        'updatedat',
        'batchno',
        'fieldbalancerit',
        'fieldbalanceton',
    ];
    
    protected $casts = [
        'luasrkh' => 'decimal:2',
        'luashasil' => 'decimal:2',
        'luassisa' => 'decimal:2',
        'fieldbalancerit' => 'decimal:2',
        'fieldbalanceton' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];
    
    // ✅ NEW: Relationship to batch
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batchno', 'batchno');
    }
    
    // Relations
    public function lkhheader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhno', 'lkhno');
    }
    
    // Scopes
    public function scopeByLkh($query, $lkhno)
    {
        return $query->where('lkhno', $lkhno);
    }
    
    public function scopeByBlok($query, $blok)
    {
        return $query->where('blok', $blok);
    }
    
    public function scopeByPlot($query, $plot)
    {
        return $query->where('plot', $plot);
    }
    
    // ✅ NEW: Accessor to get lifecycle status from batch
    public function getLifecycleStatusAttribute()
    {
        return $this->batch?->lifecyclestatus;
    }
    
    // ✅ NEW: Accessor to get batch info
    public function getBatchInfoAttribute()
    {
        if (!$this->batch) {
            return null;
        }
        
        return [
            'batchno' => $this->batch->batchno,
            'lifecyclestatus' => $this->batch->lifecyclestatus,
            'batchdate' => $this->batch->batchdate,
            'tanggalpanen' => $this->batch->tanggalpanen,
            'batcharea' => $this->batch->batcharea,
        ];
    }
    
    // Helper methods
    public function getCompletionPercentage()
    {
        if ($this->luasrkh <= 0) {
            return 0;
        }
        
        return ($this->luashasil / $this->luasrkh) * 100;
    }
    
    public function isCompleted()
    {
        return $this->luassisa <= 0;
    }
    
    public function getFormattedBlokPlot()
    {
        return $this->blok . '-' . $this->plot;
    }
    
    // ✅ UPDATED: Use batch relationship for panen info
    public function isPanenPlot()
    {
        return !is_null($this->batchno);
    }
    
    public function getHaritebangAttribute()
    {
        if (!$this->batch || !$this->batch->tanggalpanen) {
            return null;
        }
        
        $lkhDate = $this->lkhheader?->lkhdate ?? now();
        return Carbon::parse($lkhDate)->diffInDays($this->batch->tanggalpanen) + 1;
    }
}