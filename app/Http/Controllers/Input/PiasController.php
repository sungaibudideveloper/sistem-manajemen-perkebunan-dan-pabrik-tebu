<?php

namespace App\Http\Controllers\Input;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\usematerialhdr;
use App\Models\usemateriallst;
use App\Models\HerbisidaDosage;
use App\Models\Herbisida;
use App\Models\Rkhhdr;
use App\Models\Rkhlst;
use App\Models\piashdr;
use App\Models\piaslst;
use Carbon\Carbon;

class PiasController extends Controller
{
 
    public function __construct()
    {
        View::share([
            'navbar' => 'Input',
            'nav' => 'gudang',
            'routeName' => route('input.gudang.index'),
        ]);
    }

    public function home(Request $request)
    {   
        $rkhhdr = new rkhhdr;
        $data = $rkhhdr
        ->leftJoin('user as u', 'u.userid', '=', 'rkhhdr.mandorid')
        ->where('approvalstatus', 1)
        ->select(
            'rkhhdr.*', 
            'u.name as mandor_name'
        )
        ->paginate(15);
        return view('input.pias.home')->with([
            'title'       => 'Pias',
            'data'        => $data
        ]);
    }

    public function detail(Request $request)
    {   
        $rkhhdr = new rkhhdr;
        $rkhlst = new rkhlst;
        $lkhhdr = new rkhhdr;
        $lkhlst = new rkhlst;

        $data = $rkhhdr
        ->leftJoin('user as u', 'u.userid', '=', 'rkhhdr.mandorid')
        ->leftJoin('lkhhdr', 'lkhhdr.rkhno', '=', 'rkhhdr.rkhno')
        ->leftJoin('lkhdetailplot','lkhdetailplot.lkhno', '=', 'lkhhdr.lkhno')
        ->leftJoin('masterlist', function($join) {
            $join->on('masterlist.companycode', '=', 'rkhhdr.companycode')
                 ->on('masterlist.blok', '=', 'lkhdetailplot.blok')
                 ->on('masterlist.plot', '=', 'lkhdetailplot.plot');
        })
        ->where('rkhhdr.rkhno', $request->input('rkhno'))
        ->where('approvalstatus', 1)
        ->select(
            'rkhhdr.*', 
            'u.name as mandor_name',
            'lkhhdr.lkhno',
            'lkhdetailplot.blok',
            'lkhdetailplot.plot',
            'lkhdetailplot.luasrkh',
            'masterlist.tanggalulangtahun',
            'masterlist.kodevarietas',
            'masterlist.kodestatus',
            'masterlist.batchno'
        )
        ->get();
        
        // dd($data);

        return view('input.pias.detail')->with([
            'title'         => 'Pias',
            'data'        => $data
        ]);
    }

    


    public function submit(Request $request)
{
    $data = $request->validate([
        'rkhno'   => 'required|string|max:20',
        'inputTJ' => 'required|numeric|min:1',
        'inputTC' => 'required|numeric|min:1',
    ], [
        'rkhno.required'   => 'RKH No harus diisi',
        'inputTJ.required' => 'Total TJ tidak boleh kosong',
        'inputTJ.min'      => 'Total TJ harus lebih besar atau sama dengan 1',
        'inputTC.required' => 'Total TC tidak boleh kosong',
        'inputTC.min'      => 'Total TC harus lebih besar atau sama dengan 1',
    ]);

    $rkhno  = $data['rkhno'];
    $stokTJ = (float) $data['inputTJ'];
    $stokTC = (float) $data['inputTC'];

    // Ambil plot2 dari DB (banyak baris per RKH)
    $rowsDb = DB::table('rkhhdr')
        ->leftJoin('lkhhdr', 'lkhhdr.rkhno', '=', 'rkhhdr.rkhno')
        ->leftJoin('lkhdetailplot','lkhdetailplot.lkhno', '=', 'lkhhdr.lkhno')
        ->leftJoin('masterlist', function($join) {
            $join->on('masterlist.companycode', '=', 'rkhhdr.companycode')
                 ->on('masterlist.blok', '=', 'lkhdetailplot.blok')
                 ->on('masterlist.plot', '=', 'lkhdetailplot.plot');
        })
        ->where('rkhhdr.rkhno', $rkhno)
        ->where('approvalstatus', 1)
        ->select(
            'rkhhdr.companycode',
            'rkhhdr.rkhdate',
            'lkhhdr.lkhno',
            'lkhdetailplot.blok',
            'lkhdetailplot.plot',
            'lkhdetailplot.luasrkh',
            'masterlist.tanggalulangtahun'
        )
        ->get();

    if ($rowsDb->isEmpty()) {
        return back()->withErrors(['data' => 'Data plot untuk RKH ini tidak ditemukan / belum di-approve.'])->withInput();
    }

    $companycode = $rowsDb->first()->companycode ?? (auth()->user()->companycode ?? 'DEFAULT');

    // Persentase bulan 1..10
    $pcts = [
        1=>['tj'=>0.70,'tc'=>0.30], 2=>['tj'=>0.70,'tc'=>0.30], 3=>['tj'=>0.69,'tc'=>0.40],
        4=>['tj'=>0.50,'tc'=>0.50], 5=>['tj'=>0.40,'tc'=>0.60], 6=>['tj'=>0.30,'tc'=>0.70],
        7=>['tj'=>0.30,'tc'=>0.70], 8=>['tj'=>0.30,'tc'=>0.70], 9=>['tj'=>0.30,'tc'=>0.70],
        10=>['tj'=>0.30,'tc'=>0.70],
    ];

    $needsTJ = []; $needsTC = []; $rows = [];

    foreach ($rowsDb as $row) {
        $luas  = (float) ($row->luasrkh ?? 0);
        $umur  = 0;
        if ($row->rkhdate && $row->tanggalulangtahun) {
            $umur = abs(Carbon::parse($row->rkhdate)->diffInDays(Carbon::parse($row->tanggalulangtahun)));
        }
        $bulan = max(1, min(10, (int)ceil($umur/30)));
        $p     = $pcts[$bulan] ?? ['tj'=>0.5,'tc'=>0.5];

        $total  = $luas * 25;
        $needTJ = $total * $p['tj'];
        $needTC = $total * $p['tc'];

        $rows[] = [
            'companycode' => $companycode,
            'rkhno'       => $rkhno,
            'lkhno'       => $row->lkhno,
            'blok'        => $row->blok,
            'plot'        => $row->plot,
            'needTJ'      => $needTJ,
            'needTC'      => $needTC,
        ];

        $needsTJ[] = $needTJ;
        $needsTC[] = $needTC;
    }

    // Alokasi equal-share (stok cukup → equals needs)
    $allocTJ = $this->allocateEqual($needsTJ, $stokTJ);
    $allocTC = $this->allocateEqual($needsTC, $stokTC);

    $sumNeedTJ  = array_sum($needsTJ);
    $sumNeedTC  = array_sum($needsTC);
    $sumAllocTJ = array_sum($allocTJ);
    $sumAllocTC = array_sum($allocTC);

    // Status pakai truncate 3 desimal (tanpa rounding)
    $tjOk = $this->floor3($sumAllocTJ) >= $this->floor3($sumNeedTJ);
    $tcOk = $this->floor3($sumAllocTC) >= $this->floor3($sumNeedTC);
    //dd($allocTJ, $allocTC, $sumNeedTJ, $sumNeedTC, $sumAllocTJ, $sumAllocTC, $tjOk, $tcOk, $rows);
    // DB::beginTransaction();
    // try {
        // refresh detail sesuai skema piaslst (hanya field yang ada)
        piaslst::where('companycode', $companycode)->where('rkhno', $rkhno)->delete();

        foreach ($rows as $i => $r) {
            piaslst::create([
                'companycode' => $r['companycode'],
                'rkhno'       => $r['rkhno'],
                'lkhno'       => $r['lkhno'],
                'blok'        => $r['blok'],
                'plot'        => $r['plot'],
                'tj'          => $allocTJ[$i],   // alokasi TJ untuk plot ini
                'tc'          => $allocTC[$i],   // alokasi TC untuk plot ini
            ]);
        }

        // header sesuai skema piashdr (stok + status)
        piashdr::updateOrCreate(
            ['companycode'=>$companycode, 'rkhno'=>$rkhno],
            [
                'generateddate' => now(),
                'tj'            => $stokTJ,     // stok input TJ
                'tc'            => $stokTC,     // stok input TC
                'tjstatus'      => $tjOk ? 1 : 0,
                'tcstatus'      => $tcOk ? 1 : 0,
                'inputby'       => auth()->user()->name ?? 'System',
                'updateby'      => auth()->user()->name ?? 'System',
            ]
        );

        // DB::commit();
        return back()->with('success','Data pias berhasil disimpan');
    // } catch (\Throwable $e) {
    //     DB::rollBack();
    //     return back()->withErrors(['server'=>'Gagal menyimpan data: '.$e->getMessage()])->withInput();
    // }
}

/** truncate 3 desimal tanpa pembulatan */
private function floor3(float $v): float
{
    return floor($v * 1000) / 1000;
}

/** alokasi stok equal-share; stok cukup → kembalikan needs apa adanya */
private function allocateEqual(array $needs, float $stock): array
{
    $n = count($needs);
    if ($n === 0 || $stock <= 0) return array_fill(0, $n, 0.0);

    $totalNeed = array_sum($needs);
    if ($stock >= $totalNeed) return $needs;

    $alloc  = array_fill(0, $n, 0.0);
    $remain = $stock;
    $active = array_keys(array_filter($needs, fn($v)=>$v>0));

    while ($remain > 0 && !empty($active)) {
        $share = $remain / count($active);
        $next  = [];
        foreach ($active as $i) {
            $gap  = $needs[$i] - $alloc[$i];
            $give = min($gap, $share);
            $alloc[$i] += $give;
            $remain    -= $give;
            if ($needs[$i] - $alloc[$i] > 1e-12) $next[] = $i;
        }
        if (count($next) === count($active)) break;
        $active = $next;
    }
    return $alloc;
}



    // public function submit(Request $request) 
    // {
    //     $request->validate([
    //         'inputTJ' => 'required|numeric|min:0',
    //         'inputTC' => 'required|numeric|min:0',
    //         'rkhno' => 'required|string'
    //     ]);
    
    //     // Data yang diterima dari frontend
    //     $stokTJ = $request->inputTJ;
    //     $stokTC = $request->inputTC;
    //     $rkhno = $request->rkhno;
    //     $allocations = $request->allocations; // Array hasil perhitungan JS
        
    //     dd($request);

    //     DB::beginTransaction();
        
    //     try {
    //         // 1. Simpan ke tabel piashdr (header)
    //         $piashdr = new piashdr();
    //         $piashdr->rkhno = $rkhno;
    //         $piashdr->companycode = auth()->user()->companycode ?? 'DEFAULT';
    //         $piashdr->generateddate = now();
    //         $piashdr->total_tj = $stokTJ;
    //         $piashdr->total_tc = $stokTC;
    //         $piashdr->inputby = auth()->user()->name ?? 'System';
    //         $piashdr->updateby = auth()->user()->name ?? 'System';
    //         $piashdr->save();
    
    //         // 2. Simpan ke tabel piaslst (detail per plot)
    //         foreach ($allocations as $allocation) {
    //             $piaslst = new piaslst();
    //             $piaslst->rkhno = $rkhno;
    //             $piaslst->blok = $allocation['blok'];
    //             $piaslst->plot = $allocation['plot'];
    //             $piaslst->alokasi_tj = $allocation['tj'];
    //             $piaslst->alokasi_tc = $allocation['tc'];
    //             $piaslst->kebutuhan_tj = $allocation['needTJ'];
    //             $piaslst->kebutuhan_tc = $allocation['needTC'];
    //             $piaslst->luas = $allocation['luas'];
    //             $piaslst->umur_hari = $allocation['umur'];
    //             $piaslst->save();
    //         }
    
    //         DB::commit();
            
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data pias berhasil disimpan'
    //         ]);
    
    //     } catch (\Exception $e) {
    //         DB::rollback();
            
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal menyimpan data: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    


}
