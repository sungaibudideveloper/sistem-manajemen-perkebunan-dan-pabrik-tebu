<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * DthReportRepository
 * 
 * Handles DTH (Distribusi Tenaga Harian) report queries
 */
class DthReportRepository
{
    /**
     * Get company info
     * 
     * @param string $companycode
     * @return string
     */
    public function getCompanyInfo(string $companycode): string
    {
        $company = DB::table('company')
            ->where('companycode', $companycode)
            ->select('companycode', 'name')
            ->first();
        
        return $company ? "{$company->companycode} - {$company->name}" : $companycode;
    }

    /**
     * Get RKH numbers for specific date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getRkhNumbersByDate(string $companycode, string $date): array
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $date)
            ->pluck('rkhno')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get RKH approval statistics for date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getRkhApprovalStats(string $companycode, string $date): array
    {
        $total = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $date)
            ->count();

        $approved = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $date)
            ->where('approvalstatus', '1')
            ->count();

        $percentage = $total > 0 ? round(($approved / $total) * 100) : 0;

        return [
            'total' => $total,
            'approved' => $approved,
            'percentage' => $percentage
        ];
    }

    /**
     * Get Harian worker data for DTH
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getHarianData(string $companycode, string $date): Collection
    {
        $rkhPlots = DB::table('rkhhdr as h')
            ->join('rkhlst as l', function($join) {
                $join->on('h.id', '=', 'l.rkhhdrid');
            })
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->whereIn('l.jenistenagakerja', [1, 3]) // Harian (1) + Operator (3)
            ->select([
                'h.rkhno',
                'h.mandorid',
                'u.name as mandor_nama',
                'l.activitycode',
                'l.blok',
                'l.plot',
                'l.luasarea',
                'l.jenistenagakerja',
                'a.activityname'
            ])
            ->get();

        $result = [];
        
        foreach ($rkhPlots as $plot) {
            $workerData = DB::table('rkhlstworker')
                ->where('companycode', $companycode)
                ->where('rkhno', $plot->rkhno)
                ->where('activitycode', $plot->activitycode)
                ->first();
            
            $result[] = (object)[
                'rkhno' => $plot->rkhno,
                'blok' => $plot->blok,
                'plot' => $plot->plot,
                'luasarea' => $plot->luasarea,
                'jumlahlaki' => $workerData ? ($workerData->jumlahlaki ?? 0) : 0,
                'jumlahperempuan' => $workerData ? ($workerData->jumlahperempuan ?? 0) : 0,
                'jumlahtenagakerja' => $workerData ? ($workerData->jumlahtenagakerja ?? 0) : 0,
                'jenistenagakerja' => $plot->jenistenagakerja,
                'mandor_nama' => $plot->mandor_nama,
                'activityname' => $plot->activityname
            ];
        }
        
        return collect($result);
    }

    /**
     * Get Borongan worker data for DTH
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getBoronganData(string $companycode, string $date): Collection
    {
        $rkhPlots = DB::table('rkhhdr as h')
            ->join('rkhlst as l', function($join) {
                $join->on('h.id', '=', 'l.rkhhdrid');
            })
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->where('l.jenistenagakerja', 2) // Borongan
            ->select([
                'h.rkhno',
                'h.mandorid',
                'u.name as mandor_nama',
                'l.activitycode',
                'l.blok',
                'l.plot',
                'l.luasarea',
                'l.jenistenagakerja',
                'a.activityname'
            ])
            ->get();

        $result = [];
        
        foreach ($rkhPlots as $plot) {
            $workerData = DB::table('rkhlstworker')
                ->where('companycode', $companycode)
                ->where('rkhno', $plot->rkhno)
                ->where('activitycode', $plot->activitycode)
                ->first();
            
            $result[] = (object)[
                'rkhno' => $plot->rkhno,
                'blok' => $plot->blok,
                'plot' => $plot->plot,
                'luasarea' => $plot->luasarea,
                'jumlahlaki' => $workerData ? ($workerData->jumlahlaki ?? 0) : 0,
                'jumlahperempuan' => $workerData ? ($workerData->jumlahperempuan ?? 0) : 0,
                'jumlahtenagakerja' => $workerData ? ($workerData->jumlahtenagakerja ?? 0) : 0,
                'jenistenagakerja' => $plot->jenistenagakerja,
                'mandor_nama' => $plot->mandor_nama,
                'activityname' => $plot->activityname
            ];
        }
        
        return collect($result);
    }

    /**
     * Get Alat (kendaraan) data for DTH
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getAlatData(string $companycode, string $date): Collection
    {
        return DB::table('rkhhdr as h')
            ->join('rkhlstkendaraan as rk', function($join) {
                $join->on('h.id', '=', 'rk.rkhhdrid');
            })
            ->join('rkhlst as l', function($join) {
                $join->on('h.id', '=', 'l.rkhhdrid')
                     ->on('rk.activitycode', '=', 'l.activitycode');
            })
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->leftJoin('tenagakerja as operator', function($join) use ($companycode) {
                $join->on('rk.operatorid', '=', 'operator.tenagakerjaid')
                     ->where('operator.companycode', '=', $companycode);
            })
            ->leftJoin('tenagakerja as helper', function($join) use ($companycode) {
                $join->on('rk.helperid', '=', 'helper.tenagakerjaid')
                     ->where('helper.companycode', '=', $companycode);
            })
            ->leftJoin('kendaraan as k', function($join) use ($companycode) {
                $join->on('rk.kendaraanid', '=', 'k.id')
                     ->where('k.companycode', '=', $companycode)
                     ->where('k.isactive', '=', 1);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->select([
                'h.rkhno',
                'l.blok',
                'l.plot',
                'l.luasarea',
                'rk.operatorid',
                'rk.helperid',
                'u.name as mandor_nama',
                'a.activityname',
                'operator.nama as operator_nama',
                'helper.nama as helper_nama',
                'k.nokendaraan',
                'k.jenis'
            ])
            ->get();
    }
}