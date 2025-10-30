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
        'kodestatus',
        'subkontraktorid',
    ];
    
    protected $casts = [
        'luasrkh' => 'decimal:2',
        'luashasil' => 'decimal:2',
        'luassisa' => 'decimal:2',
        'createdat' => 'datetime',
    ];
    
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
    
    // Helper methods - UPDATED column names
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
    
    public function calculateBoronganUpah($activitygroup, $companycode, $workDate)
    {
        $wageRates = Upah::getWageRates($companycode, $activitygroup, $workDate);
        
        if (isset($wageRates['PER_HECTARE'])) {
            return $this->luashasil * $wageRates['PER_HECTARE'];
        } elseif (isset($wageRates['PER_KG'])) {
            // For harvest activities, assume luashasil represents weight
            return $this->luashasil * $wageRates['PER_KG'];
        }
        
        return 0;
    }
}