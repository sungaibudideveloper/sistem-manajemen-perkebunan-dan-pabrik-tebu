// ============================================
// FILE 2: app/Models/RkhPanenLst.php
// ============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RkhPanenLst extends Model
{
    protected $table = 'rkhpanenlst';
    
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

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

    protected $casts = [
        'rencananetto' => 'decimal:2',
        'rencanaha' => 'decimal:2',
        'estimasiyph' => 'decimal:2',
        'tenagatebangjumlah' => 'integer',
        'tenagamuatjumlah' => 'integer',
        'armadawl' => 'integer',
        'armadaumum' => 'integer',
        'mesinpanen' => 'boolean',
        'grabloader' => 'boolean',
    ];

    /**
     * Relationship: Belongs to RKH Panen Header
     */
    public function rkhpanen()
    {
        return $this->belongsTo(RkhPanenHdr::class, 'rkhpanenno', 'rkhpanenno')
            ->where('companycode', $this->companycode);
    }

    /**
     * Relationship: Belongs to Kontraktor (assuming you have Kontraktor model)
     * If not, you can remove this or create the model later
     */
    public function kontraktor()
    {
        return $this->belongsTo(\App\Models\Kontraktor::class, 'kontraktorid', 'kontraktorid');
    }

    /**
     * Scope: Filter by jenis panen
     */
    public function scopeByJenisPanen($query, $jenis)
    {
        return $query->where('jenispanen', $jenis);
    }

    /**
     * Scope: Manual panen only
     */
    public function scopeManual($query)
    {
        return $query->where('jenispanen', 'MANUAL');
    }

    /**
     * Scope: Semi mekanis only
     */
    public function scopeSemiMekanis($query)
    {
        return $query->where('jenispanen', 'SEMI_MEKANIS');
    }

    /**
     * Scope: Mekanis only
     */
    public function scopeMekanis($query)
    {
        return $query->where('jenispanen', 'MEKANIS');
    }

    /**
     * Get formatted jenis panen for display
     */
    public function getFormattedJenisPanenAttribute()
    {
        return str_replace('_', '-', $this->jenispanen);
    }

    /**
     * Get jenis panen badge color
     */
    public function getJenisPanenBadgeColorAttribute()
    {
        return match($this->jenispanen) {
            'MANUAL' => 'bg-blue-500',
            'SEMI_MEKANIS' => 'bg-yellow-500',
            'MEKANIS' => 'bg-green-500',
            default => 'bg-gray-500',
        };
    }

    /**
     * Get total armada
     */
    public function getTotalArmadaAttribute()
    {
        return $this->armadawl + $this->armadaumum;
    }

    /**
     * Get total tenaga
     */
    public function getTotalTenagaAttribute()
    {
        return ($this->tenagatebangjumlah ?? 0) + ($this->tenagamuatjumlah ?? 0);
    }

    /**
     * Check if using alat mekanis
     */
    public function isUsingMekanis()
    {
        return $this->mesinpanen || $this->grabloader;
    }

    /**
     * Get alat mekanis list
     */
    public function getAlatMekanisList()
    {
        $alat = [];
        if ($this->mesinpanen) {
            $alat[] = 'Mesin Panen';
        }
        if ($this->grabloader) {
            $alat[] = 'Grab Loader';
        }
        return $alat;
    }
}