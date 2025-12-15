<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Report;

use Illuminate\Support\Facades\DB;

/**
 * DthReportRepository
 * 
 * Handles DTH (Distribusi Tenaga Harian) report queries.
 * RULE: All queries here.
 */
class DthReportRepository
{
    /**
     * Get Harian data (jenistenagakerja 1,3)
     * 
     * @param string $companycode
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    public function getHarianData($companycode, $date)
    {
        $rkhPlots = DB::table('rkhhdr as h')
            ->join('rkhlst as l', function($join) {
                $join->on('h.rkhno', '=', 'l.rkhno')
                    ->on('h.companycode', '=', 'l.companycode');
            })
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->whereIn('l.jenistenagakerja', [1, 3])
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
     * Get Borongan data (jenistenagakerja 2)
     * 
     * @param string $companycode
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    public function getBoronganData($companycode, $date)
    {
        $rkhPlots = DB::table('rkhhdr as h')
            ->join('rkhlst as l', function($join) {
                $join->on('h.rkhno', '=', 'l.rkhno')
                    ->on('h.companycode', '=', 'l.companycode');
            })
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->where('l.jenistenagakerja', 2)
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
     * Get Alat data (vehicles + operators)
     * 
     * @param string $companycode
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    public function getAlatData($companycode, $date)
    {
        return DB::table('rkhhdr as h')
            ->join('rkhlstkendaraan as rk', function($join) {
                $join->on('h.rkhno', '=', 'rk.rkhno')
                    ->on('h.companycode', '=', 'rk.companycode');
            })
            ->join('rkhlst as l', function($join) {
                $join->on('h.rkhno', '=', 'l.rkhno')
                    ->on('h.companycode', '=', 'l.companycode')
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
                $join->on('rk.nokendaraan', '=', 'k.nokendaraan')
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

    /**
     * Get RKH approval summary for date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getRkhApprovalSummary($companycode, $date)
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
}