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
        if(hasPermission('Menu Pias')){
        $perPage = (int) $request->input('perPage', 15);
        
        // Default tanggal: 2 bulan ke belakang sampai hari ini
        $startDate = $request->input('start_date', now()->subMonths(2)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        // Search query
        $search = $request->input('search');
        
        $rkhhdr = new Rkhhdr;
        
        $selected = $rkhhdr::query()
            ->leftJoin('user as u', 'u.userid', '=', 'rkhhdr.mandorid')
            ->leftJoin('piashdr as ph', function ($join) {
                $join->on('ph.rkhno', '=', 'rkhhdr.rkhno')
                     ->on('ph.companycode', '=', 'rkhhdr.companycode');
            })
            ->where('rkhhdr.approvalstatus', 1)
            // Filter tanggal
            ->whereDate('rkhhdr.rkhdate', '>=', $startDate)
            ->whereDate('rkhhdr.rkhdate', '<=', $endDate);
        
        // Filter search
        if ($search) {
            $selected->where(function($query) use ($search) {
                $query->where('rkhhdr.rkhno', 'like', "%{$search}%")
                      ->orWhere('u.name', 'like', "%{$search}%");
            });
        }
        
        $selected->select([
                'rkhhdr.*',
                DB::raw('u.name as mandor_name'),
            ])
            ->selectRaw('CASE WHEN ph.rkhno IS NULL THEN 0 ELSE 1 END as is_generated')
            ->orderByDesc('rkhhdr.rkhdate');
        
        $data = $selected->paginate($perPage)->appends($request->query());
        
        return view('input.pias.home', [
            'title'     => 'Pias',
            'data'      => $data,
            'perPage'   => $perPage,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'search'    => $search,
        ]);
        }else{
            return redirect()->back()->with('error', 'Tidak Memiliki Izin Menu!');
        }
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

            // --- PERSENTASE PER BULAN ---
            $pcts = [
                1=>['tj'=>0.70,'tc'=>0.30], 2=>['tj'=>0.70,'tc'=>0.30], 3=>['tj'=>0.60,'tc'=>0.40],
                4=>['tj'=>0.50,'tc'=>0.50], 5=>['tj'=>0.40,'tc'=>0.60], 6=>['tj'=>0.30,'tc'=>0.70],
                7=>['tj'=>0.30,'tc'=>0.70], 8=>['tj'=>0.30,'tc'=>0.70], 9=>['tj'=>0.30,'tc'=>0.70],
                10=>['tj'=>0.30,'tc'=>0.70],
            ];

            // --- HITUNG KEBUTUHAN PER PLOT ---
            $rows = [];
            foreach ($rowsDb as $row) {
                $luas    = (float) ($row->luasrkh ?? 0);
                $rkhDate = $row->rkhdate ? \Carbon\Carbon::parse($row->rkhdate) : null;
                $tut     = $row->tanggalulangtahun ? \Carbon\Carbon::parse($row->tanggalulangtahun) : null;

                if ($rkhDate && $tut) {
                    $hari  = (int) abs($rkhDate->diffInDays($tut));
                    $bulan = max(1, min(10, (int) ceil($hari / 30)));
                } else {
                    $hari  = 0;
                    $bulan = 1;
                }

                $p = $pcts[$bulan] ?? ['tj'=>0.5,'tc'=>0.5];
                $total = $luas * 25;

                $needTJ = $total * $p['tj'];
                $needTC = $total * $p['tc'];

                $rows[] = [
                    'companycode' => $companycode,
                    'rkhno'       => $rkhno,
                    'lkhno'       => $row->lkhno,
                    'blok'        => $row->blok,
                    'plot'        => $row->plot,
                    // simpan need float (untuk proporsi); versi int disimpan saat insert ke piaslst
                    'needTJ'      => $needTJ,
                    'needTC'      => $needTC,
                ];
            }

            $needsTJ = array_column($rows, 'needTJ');
            $needsTC = array_column($rows, 'needTC');
            $ids     = array_map(fn($r) => $r['blok'].'|'.$r['plot'], $rows);
            $seed    = crc32($rkhno);

            // --- ALOKASI (Equal-first + group-fair) ---
            $allocTJ = $this->allocateInt($needsTJ, $stokTJ, $seed, $ids);
            $allocTC = $this->allocateInt($needsTC, $stokTC, $seed, $ids);

            if (count($allocTJ) !== count($rows) || count($allocTC) !== count($rows)) {
                throw new \RuntimeException('Panjang alokasi tidak cocok dengan jumlah plot.');
            }

            // --- UPSERT DETAIL (needtj/needtc INT: round) ---
            $rowsInsert = [];
            foreach ($rows as $i => $r) {
                $rowsInsert[] = [
                    'companycode' => $r['companycode'],
                    'rkhno'       => $r['rkhno'],
                    'lkhno'       => $r['lkhno'],
                    'blok'        => $r['blok'],
                    'plot'        => $r['plot'],
                    'tj'          => (int) $allocTJ[$i],
                    'tc'          => (int) $allocTC[$i],
                    'needtj'      => (int) round($r['needTJ']),
                    'needtc'      => (int) round($r['needTC']),
                ];
            }

            DB::table('piaslst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();

            if (!empty($rowsInsert)) {
                DB::table('piaslst')->insert($rowsInsert);
            }

            // --- HEADER (PAKAI SUM ROUND) ---
            $needTJIntArr = array_map(static fn($v) => (int) round($v), $needsTJ);
            $needTCIntArr = array_map(static fn($v) => (int) round($v), $needsTC);
            $sumNeedTJInt = array_sum($needTJIntArr);
            $sumNeedTCInt = array_sum($needTCIntArr);

            $sumAllocTJ = array_sum($allocTJ);
            $sumAllocTC = array_sum($allocTC);

            $tjOk  = $sumAllocTJ >= $sumNeedTJInt;
            $tcOk  = $sumAllocTC >= $sumNeedTCInt;

            $sisaTJ = (int) floor($stokTJ) - $sumAllocTJ;
            $sisaTC = (int) floor($stokTC) - $sumAllocTC;

            $headerKeys = ['companycode' => $companycode, 'rkhno' => $rkhno];
            $now        = now();

            $exists = DB::table('piashdr')->where($headerKeys)->exists();

            if (!$exists) {
                DB::table('piashdr')->insert($headerKeys + [
                    'generateddate' => $now,
                    'tj'            => $stokTJ,
                    'tc'            => $stokTC,
                    'tjstatus'      => $tjOk ? 1 : 0,
                    'tcstatus'      => $tcOk ? 1 : 0,
                    'sisatj'        => $sisaTJ,  // <- pakai nama kolom Anda
                    'sisatc'        => $sisaTC,
                    'inputby'       => auth()->user()->name ?? 'System',
                ]);
            } else {
                DB::table('piashdr')->where($headerKeys)->update([
                    'tj'          => $stokTJ,
                    'tc'          => $stokTC,
                    'tjstatus'    => $tjOk ? 1 : 0,
                    'tcstatus'    => $tcOk ? 1 : 0,
                    'sisatj'      => $sisaTJ,   // <- pakai nama kolom Anda
                    'sisatc'      => $sisaTC,
                    'updateby'    => auth()->user()->name ?? 'System',
                    'updateddate' => $now,
                ]);
            }

            return back()->with('success', 'Data pias berhasil disimpan');
        });

    } catch (\Throwable $e) {
        Log::error('PIAS submit failed', [
            'rkhno' => $rkhno,
            'msg'   => $e->getMessage(),
            'trace' => substr($e->getTraceAsString(), 0, 2000),
        ]);
        return back()->withErrors(['save' => 'Gagal menyimpan PIAS: '.$e->getMessage()])->withInput();
    }
}

/**
 * Equal-first + Group-fair (CRC32; target = sum(round(need)))
 */
private function allocateInt(array $kebutuhan, float $stok, int $seed = 0, ?array $ids = null): array
{
    $n = count($kebutuhan);
    if ($n === 0) return [];

    // Normalisasi
    for ($i=0; $i<$n; $i++) {
        if (!is_finite($kebutuhan[$i]) || $kebutuhan[$i] < 0) $kebutuhan[$i] = 0.0;
    }
    if ($ids === null || count($ids) !== $n) {
        $ids = array_map(fn($i)=> (string)$i, range(0,$n-1));
    }

    // --- target & cap sinkron dgn DB ---
    $sumNeedInt = array_sum(array_map(static fn($v) => (int) round($v), $kebutuhan));
    $target     = min((int) floor($stok), (int) $sumNeedInt);
    if ($target <= 0 || $sumNeedInt <= 0) return array_fill(0, $n, 0);

    $cap = array_map(static fn($v) => (int) round($v), $kebutuhan);

    // Kuota proporsional (prioritas sekunder)
    $totalFloat = array_sum($kebutuhan);
    $kuota = [];
    for ($i=0; $i<$n; $i++) $kuota[$i] = ($totalFloat > 0) ? ($kebutuhan[$i] / $totalFloat) * $target : 0.0;

    // 1) Baseline equal split (adil), hormati cap
    $base  = intdiv($target, $n);
    $alok  = array_fill(0, $n, 0);
    for ($i=0; $i<$n; $i++) $alok[$i] = min($base, $cap[$i]);
    $remain = $target - array_sum($alok);
    if ($remain <= 0) return $alok;

    // 2) Kelompokkan berdasarkan need dibulatkan (group fair)
    $needInt = array_map(static fn($v)=>(int)round($v), $kebutuhan);
    $groups  = []; // needInt => [idx...]
    for ($i=0; $i<$n; $i++) $groups[$needInt[$i]][] = $i;
    krsort($groups, SORT_NUMERIC); // need terbesar dahulu

    // helper: urut dalam grup pakai CRC32(id)^seed, tie: pecahan kuota desc, tie: index asc
    $orderGroup = function(array $idxs) use($ids,$seed,$kuota) {
        usort($idxs, function($a,$b) use($ids,$seed,$kuota){
            $ha = (crc32($ids[$a]) ^ $seed);
            $hb = (crc32($ids[$b]) ^ $seed);
            if ($ha === $hb) {
                $fa = $kuota[$a] - floor($kuota[$a]);
                $fb = $kuota[$b] - floor($kuota[$b]);
                if ($fa === $fb) return $a <=> $b;
                return ($fa < $fb) ? 1 : -1; // frac desc
            }
            return ($ha < $hb) ? -1 : 1;   // hash asc
        });
        return $idxs;
    };

    // 3) Bagi sisa per GRUP need (merata; selisih dalam grup ≤ 1)
    while ($remain > 0) {
        $progress = false;

        foreach ($groups as $needVal => $idxsAll) {
            if ($remain <= 0) break;

            // kandidat yang masih punya ruang
            $idxs = array_values(array_filter($idxsAll, fn($i)=> $alok[$i] < $cap[$i]));
            if (empty($idxs)) continue;

            $idxs = $orderGroup($idxs);

            if ($remain >= count($idxs)) {
                foreach ($idxs as $i) $alok[$i] += 1;
                $remain -= count($idxs);
                $progress = true;
                continue;
            }

            for ($k=0; $k<$remain; $k++) {
                $i = $idxs[$k];
                $alok[$i] += 1;
            }
            $remain = 0;
            $progress = true;
            break;
        }

        if (!$progress) break;
    }

    return $alok;
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
