<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Rkh;

use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterlistBatchRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * RkhValidationService
 * 
 * Handles RKH validation business rules.
 * RULE: Can use repos for validation queries, no writes.
 */
class RkhValidationService
{
    protected $batchRepo;
    protected $masterDataRepo;

    public function __construct(
        MasterlistBatchRepository $batchRepo,
        MasterDataRepository $masterDataRepo
    ) {
        $this->batchRepo = $batchRepo;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Validate RKH request
     * Throws ValidationException on error (like original controller)
     */
    public function validateRkhRequest(Request $request)
    {
        $request->validate([
            'mandor_id'              => 'required|exists:user,userid',
            'tanggal'                => 'required|date',
            'keterangan'             => 'nullable|string|max:500',
            'rows'                   => 'required|array|min:1',
            'rows.*.blok'            => 'required|string',
            'rows.*.plot'            => 'nullable|string',
            'rows.*.nama'            => 'required|string',
            'rows.*.luas'            => 'nullable|numeric|min:0',
            'rows.*.batchno'         => 'nullable|string',
            'rows.*.kodestatus'      => 'nullable|string|in:PC,RC1,RC2,RC3',
            'rows.*.material_group_id' => 'nullable|integer',
            'workers'                  => 'required|array|min:0',
            'workers.*.laki'           => 'nullable|integer|min:0',
            'workers.*.perempuan'      => 'nullable|integer|min:0',
            'workers.*.total'          => 'required|integer|min:0',
            'kendaraan'                       => 'nullable|array',
            'kendaraan.*'                     => 'nullable|array',
            'kendaraan.*.*.nokendaraan'       => 'required_with:kendaraan.*|string',
            'kendaraan.*.*.operatorid'        => 'required_with:kendaraan.*|string',
            'kendaraan.*.*.usinghelper'       => 'nullable|in:0,1',
            'kendaraan.*.*.helperid'          => 'nullable|string',
        ]);

        $plotErrors = $this->validatePlotRequirement($request->input('rows', []));
        if (!empty($plotErrors)) {
            throw ValidationException::withMessages([
                'plot_validation' => $plotErrors
            ]);
        }

        $companycode = session('companycode') ?? auth()->user()->companycode ?? null;
        
        $plantingErrors = $this->validatePlantingPlots($request->input('rows', []), $companycode);
        if (!empty($plantingErrors)) {
            throw ValidationException::withMessages([
                'planting_validation' => $plantingErrors
            ]);
        }
        
        $panenErrors = $this->validatePanenPlots($request->input('rows', []), $companycode);
        if (!empty($panenErrors)) {
            throw ValidationException::withMessages([
                'panen_validation' => $panenErrors
            ]);
        }
    }

    /**
     * Validate plot requirement based on activity
     */
    public function validatePlotRequirement($rows)
    {
        $errors = [];
        
        foreach ($rows as $index => $row) {
            $activityCode = $row['nama'] ?? null;
            
            if (!$activityCode) continue;
            
            $activity = $this->masterDataRepo->getActivityByCode($activityCode);
            
            if ($activity && $activity->isblokactivity != 1) {
                if (empty($row['plot'])) {
                    $errors[] = "Row " . ($index + 1) . ": Plot wajib diisi untuk aktivitas {$activity->activityname}";
                }
            }
        }
        
        return $errors;
    }

    /**
     * Validate planting plots (ZPK activities)
     */
    public function validatePlantingPlots($rows, $companycode)
    {
        $errors = [];
        
        foreach ($rows as $index => $row) {
            $activityCode = $row['nama'] ?? null;
            $plot = $row['plot'] ?? null;
            
            if (!$activityCode || !$plot) continue;
            
            $activity = $this->masterDataRepo->getActivityByCode($activityCode);
            
            if (!$activity || !str_starts_with($activity->activitygroup, 'ZPK')) {
                continue;
            }
            
            $batchInfo = $this->batchRepo->getActiveBatchForPlot($companycode, $plot);
            
            if ($batchInfo) {
                $errors[] = "Row " . ($index + 1) . ": Plot {$plot} sudah memiliki batch aktif ({$batchInfo->batchno}). Tidak bisa ditanam ulang.";
            }
        }
        
        return $errors;
    }

    /**
     * Validate harvest plots (Panen activities)
     */
    public function validatePanenPlots($rows, $companycode)
    {
        $errors = [];
        
        foreach ($rows as $index => $row) {
            $activityCode = $row['nama'] ?? null;
            $plot = $row['plot'] ?? null;
            
            if (!$activityCode || !$plot) continue;
            
            $activity = $this->masterDataRepo->getActivityByCode($activityCode);
            
            if (!$activity || $activity->activitygroup !== 'Panen') {
                continue;
            }
            
            $batchInfo = $this->batchRepo->getActiveBatchForPlot($companycode, $plot);
            
            if (!$batchInfo) {
                $errors[] = "Row " . ($index + 1) . ": Plot {$plot} tidak memiliki batch aktif. Tidak bisa dipanen.";
            }
        }
        
        return $errors;
    }
}