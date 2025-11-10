<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LkhDetailBsm extends Model
{
    protected $table = 'lkhdetailbsm';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'lkhno',
        'plot',
        'batchno',
        'nilaibersih',
        'nilaisegar',
        'nilaimanis',
        'averagescore',
        'grade',
        'keterangan',
        'inputby',
        'createdat',
        'updateby',
        'updatedat'
    ];

    protected $casts = [
        'nilaibersih' => 'decimal:2',
        'nilaisegar' => 'decimal:2',
        'nilaimanis' => 'decimal:2',
        'averagescore' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    /**
     * Relationship to LKH Header
     */
    public function lkhHeader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhno', 'lkhno');
    }

    /**
     * Relationship to Batch
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batchno', 'batchno');
    }

    /**
     * Calculate grade based on average score
     * 
     * @param float $averageScore
     * @return string|null
     */
    public static function calculateGrade($averageScore)
    {
        if ($averageScore === null) {
            return null;
        }

        if ($averageScore >= 80) {
            return 'A';
        } elseif ($averageScore >= 60) {
            return 'B';
        } else {
            return 'C';
        }
    }

    /**
     * Calculate average score from B, S, M values
     * 
     * @param float|null $nilaibersih
     * @param float|null $nilaisegar
     * @param float|null $nilaimanis
     * @return float|null
     */
    public static function calculateAverageScore($nilaibersih, $nilaisegar, $nilaimanis)
    {
        $values = array_filter([$nilaibersih, $nilaisegar, $nilaimanis], function($v) {
            return $v !== null;
        });

        if (empty($values)) {
            return null;
        }

        return round(array_sum($values) / count($values), 2);
    }

    /**
     * Update BSM values and auto-calculate average & grade
     * 
     * @param array $data
     * @return bool
     */
    public function updateBsmValues($data)
    {
        $nilaibersih = $data['nilaibersih'] ?? $this->nilaibersih;
        $nilaisegar = $data['nilaisegar'] ?? $this->nilaisegar;
        $nilaimanis = $data['nilaimanis'] ?? $this->nilaimanis;

        // Auto-calculate average and grade
        $averageScore = self::calculateAverageScore($nilaibersih, $nilaisegar, $nilaimanis);
        $grade = self::calculateGrade($averageScore);

        return $this->update([
            'nilaibersih' => $nilaibersih,
            'nilaisegar' => $nilaisegar,
            'nilaimanis' => $nilaimanis,
            'averagescore' => $averageScore,
            'grade' => $grade,
            'keterangan' => $data['keterangan'] ?? $this->keterangan,
            'updateby' => auth()->user()->userid ?? 'SYSTEM',
            'updatedat' => now()
        ]);
    }

    /**
     * Scope: Only BSM records with completed values
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('nilaibersih')
                    ->whereNotNull('nilaisegar')
                    ->whereNotNull('nilaimanis')
                    ->whereNotNull('averagescore')
                    ->whereNotNull('grade');
    }

    /**
     * Scope: Only BSM records with empty values (waiting for input)
     */
    public function scopePending($query)
    {
        return $query->where(function($q) {
            $q->whereNull('nilaibersih')
              ->orWhereNull('nilaisegar')
              ->orWhereNull('nilaimanis');
        });
    }

    /**
     * Scope: Filter by grade
     */
    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }
}