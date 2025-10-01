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
        $piashdr = new piashdr;
        $piaslst = new piaslst;

        $hdr = $piashdr->where('rkhno', $request->input('rkhno'))->first();
        $lst = $piaslst->where('rkhno', $request->input('rkhno'))->get();

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
            
        return view('input.pias.detail')->with([
            'title' => 'Pias',
            'data'  => $data,
            'hdr'   => $hdr,
            'lst'   => $lst
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

    try {
        return DB::transaction(function () use ($rkhno, $stokTJ, $stokTC) {

            // --- QUERY PLOT ---
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
                ->orderBy('lkhdetailplot.blok')
                ->orderBy('lkhdetailplot.plot')
                ->get();

            if ($rowsDb->isEmpty()) {
                return back()->withErrors(['data' => 'Data plot untuk RKH ini tidak ditemukan / belum di-approve.'])->withInput();
            }

            $companycode = $rowsDb->first()->companycode ?? (auth()->user()->companycode ?? 'DEFAULT');

            // --- HITUNG KEBUTUHAN ---
            $pcts = [
                1=>['tj'=>0.70,'tc'=>0.30], 2=>['tj'=>0.70,'tc'=>0.30], 3=>['tj'=>0.69,'tc'=>0.40],
                4=>['tj'=>0.50,'tc'=>0.50], 5=>['tj'=>0.40,'tc'=>0.60], 6=>['tj'=>0.30,'tc'=>0.70],
                7=>['tj'=>0.30,'tc'=>0.70], 8=>['tj'=>0.30,'tc'=>0.70], 9=>['tj'=>0.30,'tc'=>0.70],
                10=>['tj'=>0.30,'tc'=>0.70],
            ];

            $rows = [];
            foreach ($rowsDb as $row) {
                $luas  = (float) ($row->luasrkh ?? 0);
                $hari  = (int) ($row->hari ?? 0);
                $bulan = max(1, min(10, (int) ($row->bulan_sql ?? 1)));
                $p     = $pcts[$bulan] ?? ['tj'=>0.5,'tc'=>0.5];

                $total  = $luas * 25;
                $rows[] = [
                    'companycode' => $companycode,
                    'rkhno'       => $rkhno,
                    'lkhno'       => $row->lkhno,
                    'blok'        => $row->blok,
                    'plot'        => $row->plot,
                    'needTJ'      => $total * $p['tj'],
                    'needTC'      => $total * $p['tc'],
                ];
            }

            $needsTJ = array_column($rows, 'needTJ');
            $needsTC = array_column($rows, 'needTC');

            // --- ALOKASI (pakai function-mu Hamilton) ---
            $allocTJ  = $this->allocateInt($needsTJ, $stokTJ);
            $allocTC  = $this->allocateInt($needsTC, $stokTC);

            // --- PRE-FLIGHT CHECKS ---
            if (count($allocTJ) !== count($rows) || count($allocTC) !== count($rows)) {
                throw new \RuntimeException('Panjang alokasi tidak cocok dengan jumlah plot.');
            }

            // // Jika allocateInt kamu menargetkan total = floor(stok), cek ini:
            // if (array_sum($allocTJ) !== (int)floor($stokTJ) || array_sum($allocTC) !== (int)floor($stokTC)) {
            //     dd($allocTJ, $stokTJ, $allocTC, $stokTC);
            //     throw new \RuntimeException('Total alokasi tidak sama dengan stok.');
            // }

            // --- UPSERT DETAIL ---
            $rowsInsert = [];
            foreach ($rows as $i => $r) {
                $rowsInsert[] = [
                    'companycode' => $r['companycode'],
                    'rkhno'       => $r['rkhno'],
                    'lkhno'       => $r['lkhno'],
                    'blok'        => $r['blok'],
                    'plot'        => $r['plot'],
                    'tj'          => (int)$allocTJ[$i],
                    'tc'          => (int)$allocTC[$i],
                ];
            }

            DB::table('piaslst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
          
          // 2) Bulk insert baris baru
          if (!empty($rowsInsert)) {
              DB::table('piaslst')->insert($rowsInsert); // sekali jalan (array of rows)
          }

            // --- UPDATE/INSERT HEADER (jaga generateddate & inputby) ---
            $sumNeedTJ  = array_sum($needsTJ);
            $sumNeedTC  = array_sum($needsTC);
            $sumAllocTJ = array_sum($allocTJ);
            $sumAllocTC = array_sum($allocTC);

            $tjOk = $sumAllocTJ >= (int)ceil($sumNeedTJ);
            $tcOk = $sumAllocTC >= (int)ceil($sumNeedTC);

            $headerKeys = ['companycode' => $companycode, 'rkhno' => $rkhno];
            $now        = now();

            $exists = DB::table('piashdr')->where($headerKeys)->exists();

            if (!$exists) {
                // INSERT: set generateddate & inputby saja (jangan set updateddate)
                DB::table('piashdr')->insert($headerKeys + [
                    'generateddate' => $now,
                    'tj'            => $stokTJ,
                    'tc'            => $stokTC,
                    'tjstatus'      => $tjOk ? 1 : 0,
                    'tcstatus'      => $tcOk ? 1 : 0,
                    'inputby'       => auth()->user()->name ?? 'System',
                ]);
            } else {
                // UPDATE: jangan ubah generateddate & inputby
                DB::table('piashdr')->where($headerKeys)->update([
                    'tj'          => $stokTJ,
                    'tc'          => $stokTC,
                    'tjstatus'    => $tjOk ? 1 : 0,
                    'tcstatus'    => $tcOk ? 1 : 0,
                    'updateby'    => auth()->user()->name ?? 'System',
                    'updateddate' => $now, // ganti ke 'updated_at' kalau itu nama kolommu
                ]);
            }

            // sukses → commit otomatis oleh DB::transaction
            return back()->with('success', 'Data pias berhasil disimpan');
        });

    } catch (Throwable $e) {
        Log::error('PIAS submit failed', [
            'rkhno' => $rkhno,
            'msg'   => $e->getMessage(),
            'trace' => substr($e->getTraceAsString(), 0, 2000),
        ]);
        return back()
            ->withErrors(['save' => 'Gagal menyimpan PIAS: '.$e->getMessage()])
            ->withInput();
    }
}

/** Alokasi integer (Largest Remainder), total pas = target */
private function allocateInt(array $needs, float $stock): array
{
    $n = count($needs);
    if ($n === 0) return [];

    $sumNeed = array_sum($needs);
    if ($stock <= 0 || $sumNeed <= 0) {
        return array_fill(0, $n, 0);
    }

    // Target unit yang dibagi (integer, tidak melebihi stok maupun kebutuhan)
    $target = min((int)floor($stock), (int)ceil($sumNeed));
    if ($target <= 0) return array_fill(0, $n, 0);

    // Kuota proporsional
    $quotas = [];
    for ($i = 0; $i < $n; $i++) {
        $quotas[$i] = ($needs[$i] / $sumNeed) * $target;
    }

    // Floor dasar
    $alloc  = array_map('intval', array_map('floor', $quotas));
    $remain = $target - array_sum($alloc);
    if ($remain <= 0) return $alloc;

    // Bagi sisa ke pecahan terbesar (tie-break by index biar deterministik)
    $order = [];
    for ($i = 0; $i < $n; $i++) {
        $order[] = ['i' => $i, 'frac' => $quotas[$i] - floor($quotas[$i])];
    }
    usort($order, function ($a, $b) {
        if ($a['frac'] == $b['frac']) return $a['i'] <=> $b['i'];
        return ($a['frac'] < $b['frac']) ? 1 : -1; // desc
    });

    for ($k = 0; $k < $remain && $k < $n; $k++) {
        $alloc[$order[$k]['i']] += 1;
    }

    return $alloc;
}

    

// /** truncate 3 desimal tanpa pembulatan */
// private function floor3(float $v): float
// {
//     return floor($v * 1000) / 1000;
// }

// /** alokasi stok equal-share; stok cukup → kembalikan needs apa adanya */
// private function allocateEqual(array $needs, float $stock): array
// {
//     $n = count($needs);
//     if ($n === 0 || $stock <= 0) return array_fill(0, $n, 0.0);

//     $totalNeed = array_sum($needs);
//     if ($stock >= $totalNeed) return $needs;

//     $alloc  = array_fill(0, $n, 0.0);
//     $remain = $stock;
//     $active = array_keys(array_filter($needs, fn($v)=>$v>0));

//     while ($remain > 0 && !empty($active)) {
//         $share = $remain / count($active);
//         $next  = [];
//         foreach ($active as $i) {
//             $gap  = $needs[$i] - $alloc[$i];
//             $give = min($gap, $share);
//             $alloc[$i] += $give;
//             $remain    -= $give;
//             if ($needs[$i] - $alloc[$i] > 1e-12) $next[] = $i;
//         }
//         if (count($next) === count($active)) break;
//         $active = $next;
//     }
//     return $alloc;
// }



    


}
