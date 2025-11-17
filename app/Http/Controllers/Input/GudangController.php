<?php

namespace App\Http\Controllers\Input;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Company;
use App\Models\usematerialhdr;
use App\Models\usemateriallst;
use App\Models\HerbisidaDosage;
use App\Models\Herbisida;

class GudangController extends Controller
{
 
    public function __construct()
    {
        View::share([
            'navbar' => 'Input',
            'nav' => 'gudang',
            'routeName' => route('input.gudang.index'),
        ]);
    }

    //dummy
    public function index(Request $request)
    {
        $title = "Gudang";

        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'required|integer|min:1']);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        return view('input.gudang.index')->with([
            'title'         => 'Gudang',
            'perPage'       => $perPage
        ]);
    }

        public function home(Request $request)
    {   
        if(hasPermission('Menu Gudang')){
        $usematerialhdr = new usematerialhdr; 
        $usehdr2 = $usematerialhdr->selectuse(session('companycode'));
        
        // Validasi perPage
        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'required|integer|min:1']);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        
        // Filter parameters
        $search = $request->input('search');
        $startDate = $request->input('start_date', now()->subMonths(2)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Query dengan filter
        $usehdr = usematerialhdr::from('usematerialhdr as a')
        ->join('rkhhdr as b', 'a.rkhno', '=', 'b.rkhno')
        ->join('user as c', 'b.mandorid', '=', 'c.userid')
        ->leftJoinSub(
            usemateriallst::select('rkhno', DB::raw('MAX(nouse) as nouse'))
                ->groupBy('rkhno'), 'd', 'a.rkhno', '=', 'd.rkhno'
        )
        ->where('a.companycode', session('companycode'))
        ->whereDate('a.createdat', '>=', $startDate)
        ->whereDate('a.createdat', '<=', $endDate);
        
        // Filter search
        if ($search) {
            $usehdr->where(function($q) use ($search) {
                $q->where('a.rkhno', 'like', "%{$search}%")
                ->orWhere('c.name', 'like', "%{$search}%");
            });
        }
        
        $usehdr = $usehdr->select('a.*', 'c.name', 'd.nouse')
            ->orderBy('a.createdat', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('input.gudang.home')->with([
            'title'     => 'Gudang',
            'usehdr'    => $usehdr,
            'perPage'   => $perPage,
            'search'    => $search,
            'startDate' => $startDate,
            'endDate'   => $endDate
        ]);
        }else{
            return redirect()->back()->with('error', 'Tidak Memiliki Izin Menu!');
        }
    }

    public function detail(Request $request)
    {   
        $usematerialhdr = new usematerialhdr;
        $usemateriallst = new usemateriallst;
        $dosage = new HerbisidaDosage;
        $herbisida = new Herbisida;

        $dosage = HerbisidaDosage::get();

        $validItemCodes = HerbisidaDosage::get()->pluck('itemcode')->unique();

        $itemlist = DB::table('herbisidadosage as d')
        ->join('herbisida as h', function ($join) {
            $join->on('d.itemcode', '=', 'h.itemcode')
                 ->on('d.companycode', '=', 'h.companycode');
        })
        ->join('herbisidagroup as hg', 'd.herbisidagroupid', '=', 'hg.herbisidagroupid')
        ->where('d.companycode', session('companycode'))
        ->select(
            'd.itemcode',
            'd.dosageperha',
            'h.itemname',
            'h.measure',
            'd.herbisidagroupid',
            'hg.herbisidagroupname',
            'hg.activitycode',
            'hg.description'
        )
        ->orderBy('d.herbisidagroupid')
        ->orderBy('d.itemcode')
        ->orderBy('d.dosageperha')
        ->get();
            
        $details = collect($usematerialhdr->selectusematerial(session('companycode'), $request->rkhno,1));
        $first = $details->first();
        $detailmaterial2 = collect($usemateriallst->where('rkhno', $request->rkhno)->where('companycode',session('companycode'))->orderBy('lkhno')->orderBy('plot')->get()); 
        $detailmaterial = collect($usemateriallst->select('usemateriallst.*', 'lkhdetailplot.luasrkh')
        ->leftJoin('lkhdetailplot', function($join) {
            $join->on('usemateriallst.lkhno', '=', 'lkhdetailplot.lkhno')
                ->on('usemateriallst.plot', '=', 'lkhdetailplot.plot');})
        ->where('rkhno', $request->rkhno)->where('usemateriallst.companycode',session('companycode'))->orderBy('lkhno')->orderBy('plot')->get());

        $groupIds = $details->pluck('herbisidagroupid')->unique(); 
        $lst = usemateriallst::where('rkhno', $request->rkhno)->where('companycode',session('companycode'))->get();

        //api_costcenter
        $companyinv = company::where('companycode', session('companycode'))->first();
        $response = Http::withoutVerifying()->withOptions(['headers' => ['Accept' => 'application/json']])
        ->asJson()
        ->get('https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/costcenter_api', [
            'connection' => '172.17.1.39',
            'company' => $companyinv->companyinventory,
            'factory' => $first->factoryinv
        ]);
        
        $costcenter = collect($response->json('costcenter'));


        return view('input.gudang.detail')->with([
            'title'         => 'Gudang',
            'details'       => $details,
            'dosage'        => $dosage,
            'lst'           => $lst,
            'itemlist'      => $itemlist,
            'costcenter'    => $costcenter,
            'detailmaterial'=> $detailmaterial
        ]);
    }
 
    public function retur(Request $request)
    {   
        $usematerialhdr = new usematerialhdr;
        $usemateriallst = new usemateriallst;
        $header = $usematerialhdr->selectuse(session('companycode'), $request->rkhno,1)->get();
        $hfirst = $header->first();

        $details = usemateriallst::where('rkhno', $hfirst->rkhno)->where('lkhno', $request->lkhno)->where('itemcode', $request->itemcode)->where('plot', $request->plot);
        $first = $details->first();

        if( strtoupper($hfirst->flagstatus) == 'COMPLETED' ){
            return redirect()->back()->with('error', 'Status Barang Sudah Selesai');
        } 
        // Validasi status HARUS salah satu dari ini untuk bisa retur
        $allowedStatuses = ['UPLOADED','RECEIVED_BY_MANDOR', 'RETURNED_BY_MANDOR', 'RETURN_RECEIVED'];
        if (!in_array(strtoupper($hfirst->flagstatus), $allowedStatuses)) {
            return redirect()->back()->with('error', 'Tidak Bisa Retur! Status harus UPLOAD atau RECEIVED. Status sekarang: ' . $hfirst->flagstatus);
        }
        if(empty($first)){ 
            return redirect()->back()->with('error', 'Item Tidak ditemukan!');
        }
        if (!filled($first->nouse)) {
            return back()->with('error', 'Tidak bisa retur: Nomor USE (nouse) kosong.');
        }
        if($first->qtyretur<=0){
            return redirect()->back()->with('error', 'Tidak Bisa Retur! Qty Retur Kosong');
        } 
        if($first->qtyretur>$first->qty){
            return redirect()->back()->with('error', 'Tidak Bisa Retur! Qty Retur'.$first->qtyretur.' Lebih Besar Dari Qty Kirim'.$first->qty);
        }
        if($first->noretur != null){
            return redirect()->back()->with('error', 'Cant Retur! No Retur Not Empty');
        }
        
        

        $isi = collect();
        $isi->push((object)[
            'CompCodeTerima' => $hfirst->companyinv, 
            'FactoryTerima'  => $hfirst->factoryinv,
            'ItemGrup'       => substr($first->itemcode, 0, 2),
            'CompItemcode'   => substr($first->itemcode, 2),
            'prunit'         => $first->unit,
            'itemprice'      =>  0,
            'currcode'       => 'IDR',
            'itemnote'       => $first->itemname,
            'qtybpb'         => $first->qtyretur,
            'Keterangan'     => 'Rkhno: '.$first->rkhno.', Mandor: '.$hfirst->mandorname ?? '',  
            'vehiclenumber'  => '',
            'flagstatus'     => 'ACTIVE'
        ]);
//172.17.1.39
        $companyinv = company::where('companycode', session('companycode'))->first();
        $response = Http::withOptions([
            'headers' => ['Accept' => 'application/json']
        ])->asJson()
        ->post('https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/returuse_api', [
            'connection' => 'TESTING',
            'company' => $companyinv->companyinventory,
            'factory' => $hfirst->factoryinv,
            'isi' => $isi,  
            'userid' => auth::user()->userid,
            'nouse' => $first->nouse 
        ]); 

        //log
        if ($response->successful()) {
            Log::info('API success:', $response->json());
        } else {
            Log::error('API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
            //success update nouse
            if($response->status()==200){ 
                if($response->json()['status']==1){
                usemateriallst::where('rkhno', $request->rkhno)->where('companycode',session('companycode'))->where('itemcode', $first->itemcode)
                ->where('lkhno',$first->lkhno)->where('plot',$first->plot)->update(['noretur' => $response->json()['noretur']]);
                }
            }else{
                dd($response->json(), $response->body(), $response->status());
            }

        return redirect()->back()->with('success1', 'Sukses Membuat Dokumen Retur '. $response->json()['noretur']);
    }

    // Panggil fungsi retur yang SUDAH ADA, satu per baris usemateriallst
    public function returAll(Request $request)
    {
        $rows = usemateriallst::where('companycode', session('companycode'))
            ->where('rkhno', $request->rkhno)
            ->whereNull('noretur')
            ->where('qtyretur', '>', 0)
            ->get(['rkhno','lkhno','itemcode','plot']);

        $ok = 0; $fail = 0;

        foreach ($rows as $row) {
            // bentuk sub-request sesuai parameter yang dibutuhkan fungsi retur()
            $sub = new \Illuminate\Http\Request([
                'rkhno'    => $row->rkhno,
                'lkhno'    => $row->lkhno,
                'itemcode' => $row->itemcode,
                'plot'     => $row->plot,
            ]);

            try {
                $resp = $this->retur($sub); // pakai fungsi retur() yang sudah ada
                // anggap retur() redirect back; hitung sukses jika tidak melempar exception
                $ok++;
            } catch (\Throwable $e) {
                \Log::error('retur_bulk error', ['item'=>$row->itemcode, 'err'=>$e->getMessage()]);
                $fail++;
            }
        }

        return back()->with($ok ? 'success1' : 'error', "Retur massal selesai. Sukses: {$ok}, Gagal: {$fail}");
    }


    public function submit(Request $request)
    {
    //kunci proses di cache agar ga dobel submit 
    $lockKey = 'submit_lock_' . session('companycode') . '_' . $request->rkhno;
    if (Cache::has($lockKey)) {
        return redirect()->back()->with('error', 'Sedang memproses request sebelumnya. Mohon tunggu...');
    }
    Cache::put($lockKey, true, 10);
        // Validasi basic
        $details = collect((new usematerialhdr)->selectusematerial(session('companycode'), $request->rkhno, 1));
        $first = $details->first();
        
        if (strtoupper($first->flagstatus) != 'ACTIVE') {
            Cache::forget($lockKey);
            throw new \Exception('Tidak Dapat Edit! Item Sudah Tidak Lagi ACTIVE');
        } 
        if ($details->whereNotNull('nouse')->count() >= 1){
            Cache::forget($lockKey);
            throw new \Exception('Tidak Dapat Edit! Silahkan Retur');
        }
    
        // Validasi duplikat: lkhno + plot + itemcode
        foreach ($request->itemcode as $lkhno => $items) {
            foreach ($items as $itemcode => $plots) {
                // Group by plot untuk itemcode tertentu di lkhno tertentu
                $plotsForThisItem = array_keys($plots);
                $uniquePlots = array_unique($plotsForThisItem);
                
                if (count($plotsForThisItem) !== count($uniquePlots)) {
                    // Ada duplikat plot untuk itemcode yang sama di lkhno yang sama
                    $duplicatePlots = array_diff_assoc($plotsForThisItem, $uniquePlots);
                    $duplicatePlot = reset($duplicatePlots);
                    
                    Cache::forget($lockKey); // ⚠️ UNLOCK
                    return redirect()->back()->withInput()
                        ->with('error', "Duplikat! LKH $lkhno, Plot $duplicatePlot dengan Item $itemcode tidak boleh diinput lebih dari 1 kali.");
                }
            }
        }
    
        // Get existing data dengan key lkhno-itemcode
        $existingData = usemateriallst::where('rkhno', $request->rkhno)->where('companycode',session('companycode'))->get()->keyBy(function($item) {
            return $item->lkhno . '-' . $item->itemcode;
        });
    
        // Key details by lkhno untuk lookup
        $detailsByLkhno = $details->keyBy('lkhno');
        $herbisidaItems = Herbisida::where('companycode', session('companycode'))->get()->keyBy('itemcode');
        
        $insertData = [];
        $apiPayload = [];
        $qtyByItemcode = [];
        $itemDetails = [];
    
        // Process flat - langsung dari request
        foreach ($request->itemcode as $lkhno => $items) {
            $detail = $detailsByLkhno[$lkhno];
        
            foreach ($items as $itemcode => $keys) {
                foreach ($keys as $key => $val) {
        
                    $dosage = floatval($request->dosage[$lkhno][$itemcode][$key] ?? 0);
                    $unit   = $request->unit[$lkhno][$itemcode][$key] ?? null;
                    $luas   = $request->luas[$lkhno][$itemcode][$key] ?? 0;
                    $qtyraw    = $luas * $dosage ?? 0;
                    $qty = $qtyraw > 0 ? max(0.25, round($qtyraw / 0.25) * 0.25) : 0;
                    // $qty=round($qtyraw / 0.25) * 0.25;

                    $existingKey = $lkhno . '-' . $itemcode . '-' . $key;
                    $existing    = $existingData->get($existingKey);
        
                    $insertData[] = [
                        'companycode' => session('companycode'),
                        'rkhno'       => $request->rkhno,
                        'lkhno'       => $lkhno,
                        'itemcode'    => $itemcode,
                        'qty'         => $qty,
                        'unit'        => $unit,
                        'qtyretur'    => $existing?->qtyretur ?? 0,
                        'itemname'    => $herbisidaItems[$itemcode]->itemname ?? '',
                        'dosageperha' => $dosage,
                        'nouse'       => $existing?->nouse ?? null,
                        'plot'        => $key
                    ];
    
                    // Jumlahkan qty per itemcode
                    $qtyByItemcode[$itemcode] = ($qtyByItemcode[$itemcode] ?? 0) + $qty;
                    
                    // Simpan detail itemcode (ambil yang pertama aja)
                    if (!isset($itemDetails[$itemcode])) {
                        $itemDetails[$itemcode] = [
                            'detail' => $detail,
                            'unit' => $unit
                        ];
                    }
                }
            }
        }
        
        foreach ($qtyByItemcode as $itemcode => $totalQty) {
            $detail = $itemDetails[$itemcode]['detail'];
            $unit = $itemDetails[$itemcode]['unit'];

            $apiPayload[$itemcode] = [
                'CompCodeTerima' => $detail->companyinv,
                'FactoryTerima'  => $detail->factoryinv,
                'ItemGrup'       => substr($itemcode, 0, 2),
                'CompItemcode'   => substr($itemcode, 2),
                'prunit'         => $unit,
                'itemprice'      => 0,
                'currcode'       => 'IDR',
                'itemnote'       => $detail->herbisidagroupname,
                'qtybpb'         => round($totalQty, 3),
                'Keterangan'     => $detail->herbisidagroupname . ' - ' . $detail->name,
                'vehiclenumber'  => '',
                'flagstatus'     => $detail->flagstatus,
                'qtydigunakan'   => $detail->qtydigunakan
            ];
        }

        // Gunakan DB Transaction untuk keamanan
        DB::beginTransaction();
        
        try {
            // Delete existing records
            usemateriallst::where('rkhno', $request->rkhno)->where('companycode',session('companycode'))->delete();
            $companyinv = company::where('companycode', session('companycode'))->first();
            // Bulk insert
            usemateriallst::insert($insertData);

            // API Call
            if($details->whereNotNull('nouse')->count() < 1) {  
                $response = Http::withOptions(['headers' => ['Accept' => 'application/json']])
                    ->asJson()
                    ->post('https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/use_api', [
                        'connection' => 'TESTING',
                        'company' => $companyinv->companyinventory,
                        'factory' => $first->factoryinv,
                        'costcenter' => $request->costcenter,
                        'isi' => array_values($apiPayload),  
                        'userid' => substr(auth()->user()->userid, 0, 10)
                    ]); 
            } else {
                    DB::rollback();
                    Cache::forget($lockKey);
                    dd(
                        'MODE EDIT - Nouse sudah ada',
                        'First data:', $first,
                        'Details count:', $details->whereNotNull('nouse')->count()
                    );
            }
    
            // ✅ KEMBALIKAN: Log terpisah untuk success/error
            // DD jika response TIDAK successful
            if (!$response->successful()) {
                dd([
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload_sent' => [
                        'company' => $companyinv->companyinventory,
                        'factory' => $first->factoryinv,
                        'costcenter' => $request->costcenter,
                        'isi' => array_values($apiPayload),
                        'userid' => substr(auth()->user()->userid, 0, 10)
                    ]
                ]);
            }
            
            $responseData = $response->json();

            // Check response
            if($response->status() == 200 && $responseData['status'] == 1) {
                
                $itemPriceMap = [];
                foreach ($responseData['stockitem'] as $row) {
                    $itemcode = $row['Itemcode'] ?? null;
                    if ($itemcode) {
                        $itemPriceMap[$itemcode] = $row['Itemprice'] ?? 0;
                    }
                }
                
                
                // update nouse & itemprice
                foreach ($itemPriceMap as $itemcode => $itemprice) {
                    
                    Log::info("Before DB update:", [
                        'itemcode' => $itemcode, 
                        'itemprice' => $itemprice,
                        'type' => gettype($itemprice)
                    ]);
                    
                    usemateriallst::where('rkhno', $request->rkhno)
                        ->where('companycode', session('companycode'))
                        ->where('itemcode', $itemcode)
                        ->update([
                            'nouse'      => $responseData['noUse'],
                            'itemprice'  => $itemprice,
                            'costcenter' => $request->costcenter,
                            'startstock' => $responseData['stockitem'][$itemcode]['StartStock'] ?? 0,  
                            'endstock'   => $responseData['stockitem'][$itemcode]['EndStock'] ?? 0  
                        ]);

                    // Cek hasil di database
                    $saved = usemateriallst::where('rkhno', $request->rkhno)
                        ->where('companycode', session('companycode'))
                        ->where('itemcode', $itemcode)
                        ->value('itemprice');

                    Log::info("After DB update:", [
                        'itemcode' => $itemcode,
                        'itemprice_saved' => $saved,
                        'type' => gettype($saved)
                    ]);
                }
                 
                // Update header status
                usematerialhdr::where('rkhno', $request->rkhno)->where('companycode',session('companycode'))->update(['flagstatus' => 'DISPATCHED']);
                
                DB::commit();
                Cache::forget($lockKey);
                return redirect()->back()->with('success1', 'Data updated successfully');
                
            } else {
                DB::rollback();
                Cache::forget($lockKey);
                dd([
                    'error' => 'Response gagal 516',
                    'status' => $response->status(),
                    'responseData' => $responseData
                ]);
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            Cache::forget($lockKey);
            Log::error('Submit error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }



    public function handle(Request $request)
    {
        if ($request->has('perPage')) {
            return $this->index($request);
        }

        return $this->store($request);
    }

    protected function requestValidated(): array
    {
        return [
          'kodeaktivitas' => 'required',
          'grupaktivitas' => 'required|exists:activitygroup,activitygroup',
          'namaaktivitas' => 'required',
          'keterangan' => 'max:150',
          'var.*'       => 'required',
          'satuan.*'    => 'required'
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('activity')->where('activitycode', $request->kodeaktivitas)->exists();

        if ($exists) {
            Parent::h_flash('Kode aktivitas sudah ada dalam database.','danger');
            return redirect()->back()->withInput();
        }

        $hasil = array();
        $inputVar    = $request->var;
        $inputSatuan = $request->satuan;
        $input = [
            'activitycode'  => $request->kodeaktivitas,
            'activitygroup' => $request->grupaktivitas,
            'activityname'  => $request->namaaktivitas,
            'description'   => $request->keterangan,
            'usingmaterial' => $request->material,
            'usingvehicle'  => $request->vehicle,
            'jumlahvar'     => count($request->var),
            'createdat'     => date("Y-m-d H:i"),
            'inputby'       => Auth::user()->userid
        ];
        foreach( $request->var as $index => $value ){
            $hasil["var".$index+1] =  $value;
            $hasil["satuan".$index+1] = $inputSatuan[$index];
        }

        $input = array_merge($input, $hasil);

        try {
          DB::transaction(function () use ($input) {
              DB::table('activity')->insert($input);
          });
          Parent::h_flash('Berhasil menambahkan data.','success');
          return redirect()->back();
        } catch (\Exception $e) {
          Parent::h_flash('Error pada database, hubungi IT.','danger');
          return redirect()->back()->withInput();;
        }

        return redirect()->back();
    }

    public function update(Request $request, $activityCode)
    {
        $request->validate($this->requestValidated());

        $exists = DB::table('activity')->where('activitycode', $request->kodeaktivitas)->exists();

        if (!$exists) {
            Parent::h_flash('Data Tidak Ditemukan.','danger');
            return redirect()->back()->withInput();
        }

        DB::transaction(function () use ($request, $activityCode) {

          $input = [
              'activitycode'  => $request->kodeaktivitas,
              'activitygroup' => $request->grupaktivitas,
              'activityname'  => $request->namaaktivitas,
              'description'   => $request->keterangan,
              'jumlahvar'     => count($request->var),
              'usingmaterial' => $request->material,
              'usingvehicle'  => $request->vehicle,
              'updatedat'     => date("Y-m-d H:i"),
              'updatedby'     => Auth::user()->userid
          ];
          $hasil = array();
          $inputSatuan = $request->satuan;
          foreach( $request->var as $index => $value ){
              $hasil["var".$index+1] =  $value;
              $hasil["satuan".$index+1] = $inputSatuan[$index];
          }

          $input = array_merge($input, $hasil);

          DB::table('activity')->where('activitycode', $activityCode)->update($input);

        });

        return redirect()->route('masterdata.aktivitas.index')->with('success1', 'Data updated successfully.');
    }

    public function destroy($activityCode)
    {
        DB::transaction(function () use ($activityCode) {
            DB::table('activity')->where('activitycode', $activityCode)->delete();
        });
        Parent::h_flash('Berhasil menghapus data.', 'success');
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }








}
