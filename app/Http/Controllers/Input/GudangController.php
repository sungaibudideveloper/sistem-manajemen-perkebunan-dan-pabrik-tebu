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
    }

    public function detail(Request $request)
    {   
        $usematerialhdr = new usematerialhdr;
        $usemateriallst = new usemateriallst;
        $dosage = new HerbisidaDosage;
        $herbisida = new Herbisida;

        $dosage = HerbisidaDosage::get();

        $validItemCodes = HerbisidaDosage::get()->pluck('itemcode')->unique();

        $itemlist = DB::table('herbisidaDosage as d')
        ->join('herbisida as h', function ($join) {
            $join->on('d.itemcode', '=', 'h.itemcode')
                 ->on('d.companycode', '=', 'h.companycode');
        })
        ->where('d.companycode', session('companycode'))
        ->select(
            'd.itemcode',
            'd.dosageperha',
            'h.itemname',
            'h.measure',
            DB::raw('MIN(d.herbisidagroupid) as herbisidagroupid') // Ambil group ID pertama jika ada duplikat
        )
        ->groupBy('d.itemcode', 'd.dosageperha', 'h.itemname', 'h.measure')
        ->orderBy('d.itemcode')
        ->orderBy('d.dosageperha')
        ->get();
            
        $details = collect($usematerialhdr->selectusematerial(session('companycode'), $request->rkhno,1));
        $detailmaterial2 = collect($usemateriallst->where('rkhno', $request->rkhno)->orderBy('lkhno')->orderBy('plot')->get()); 
        $detailmaterial = collect($usemateriallst->select('usemateriallst.*', 'lkhdetailplot.luasrkh')
        ->leftJoin('lkhdetailplot', function($join) {
            $join->on('usemateriallst.lkhno', '=', 'lkhdetailplot.lkhno')
                ->on('usemateriallst.plot', '=', 'lkhdetailplot.plot');})
        ->where('rkhno', $request->rkhno)->orderBy('lkhno')->orderBy('plot')->get());

        $groupIds = $details->pluck('herbisidagroupid')->unique(); 
        $lst = usemateriallst::where('rkhno', $request->rkhno)->get();

        // $header
        $title = "Gudang";


        return view('input.gudang.detail')->with([
            'title'         => 'Gudang',
            'details'       => $details,
            'dosage'        => $dosage,
            'lst'           => $lst,
            'itemlist'      => $itemlist,
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
        
        if(empty($first)){ 
            return redirect()->back()->with('error', 'Item Tidak ditemukan!');
        }
        if($first->qtyretur<=0){
            return redirect()->back()->with('error', 'Tidak Bisa Retur! Qty Retur Kosong');
        } 
        if($first->qtyretur>$first->qty){
            return redirect()->back()->with('error', 'Tidak Bisa Retur! Qty Retur'.$first->qtyretur.' Lebih Besar Dari Qty Kirim'.$first->qty);
        }
        if( strtoupper($hfirst->flagstatus) == 'COMPLETED' ){
            return redirect()->back()->with('error', 'Status Barang Sudah Selesai');
        } 
        if( strtoupper($hfirst->flagstatus) == 'ACTIVE' || strtoupper($hfirst->flagstatus) == 'DISPATCHED' ){
            return redirect()->back()->with('error', 'Barang Belum Diterima Mandor');
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

        $response = Http::withOptions([
            'headers' => ['Accept' => 'application/json']
        ])->asJson()
        ->post('http://localhost/sbwebapp/public/app/im-purchasing/purchasing/bpb/returuse_api', [
            'connection' => 'TESTING',
            'company' => $hfirst->companyinv,
            'factory' => $hfirst->factoryinv,
            'isi' => $isi,  
            'userid' => auth::user()->userid 
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
                usemateriallst::where('rkhno', $request->rkhno)->where('itemcode', $first->itemcode)
                ->where('lkhno',$first->lkhno)->where('plot',$first->plot)->update(['noretur' => $response->json()['noretur']]);
                }
            }else{
                dd($response->json(), $response->body(), $response->status());
            }

        return redirect()->back()->with('success1', 'Sukses Membuat Dokumen Retur '. $response->json()['noretur']);
    }




    public function submit(Request $request)
    { 

              // Validasi basic
              $details = collect((new usematerialhdr)->selectusematerial(session('companycode'), $request->rkhno, 1));
              $first = $details->first();
              
              if (strtoupper($first->flagstatus) != 'ACTIVE') {
                  throw new \Exception('Tidak Dapat Edit! Item Sudah Tidak Lagi ACTIVE');
              }

             //cek lkhno
            // Validasi duplikat: lkhno + itemcode + plot
            $combinations = [];

            foreach (($request->materials ?? []) as $material) {
                $lkhno = $material['lkhno'] ?? '';
                $itemcode = $material['itemcode'] ?? '';
                $plot = $material['plot'] ?? '';
                
                $key = "$lkhno|$itemcode|$plot";
                
                if (isset($combinations[$key])) {
                    return redirect()->back()->withInput()
                        ->with('error', "Duplikat! Item $itemcode di Plot $plot sudah ada di LKH $lkhno");
                }
                
                $combinations[$key] = true;
            }

              // Get existing data dengan key lkhno-itemcode
              $existingData = usemateriallst::where('rkhno', $request->rkhno)->get()->keyBy(function($item) {
                  return $item->lkhno . '-' . $item->itemcode;
              });
              
              

              // Key details by lkhno untuk lookup
              $detailsByLkhno = $details->keyBy('lkhno');
              $herbisidaItems = Herbisida::where('companycode', session('companycode'))->get()->keyBy('itemcode');
              
              // Delete existing records
              usemateriallst::where('rkhno', $request->rkhno)->delete();
              
              $insertData = [];
              $apiPayload = [];

              // Process flat - langsung dari request
              foreach ($request->itemcode as $lkhno => $items) {
                $detail = $detailsByLkhno[$lkhno];
            
                foreach ($items as $itemcode => $keys) {
                    foreach ($keys as $key => $val) {
            
                        $dosage = floatval($request->dosage[$lkhno][$itemcode][$key] ?? 0);
                        $unit   = $request->unit[$lkhno][$itemcode][$key] ?? null;
                        $luas   = $request->luas[$lkhno][$itemcode][$key] ?? 0;
                        $qty    = $luas*$dosage ?? 0;
                        
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
                    'qtybpb'         => round($totalQty,3), // Total qty yang sudah dijumlah
                    'Keterangan'     => $detail->herbisidagroupname . ' - ' . $detail->name,
                    'vehiclenumber'  => '',
                    'flagstatus'     => $detail->flagstatus
                ];
            }

              // Bulk insert
              usemateriallst::insert($insertData);

                         
              // Filter untuk insert atau edit
              // https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/use_api
              // https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/edituse_api
              // http://localhost/sbwebapp/public/app/im-purchasing/purchasing/bpb/use_api
              // http://localhost/sbwebapp/public/app/im-purchasing/purchasing/bpb/edituse_api
              if($details->whereNotNull('nouse')->count() < 1) {  
                  // Mode insert
                  $response = Http::withOptions([
                      'headers' => ['Accept' => 'application/json']
                  ])->asJson()
                  ->post('http://localhost/sbwebapp/public/app/im-purchasing/purchasing/bpb/use_api', [
                      'connection' => 'TESTING',
                      'company' => $first->companyinv,
                      'factory' => $first->factoryinv,
                      'isi' => array_values($apiPayload),  
                      'userid' => substr(auth()->user()->userid, 0, 10)
                  ]); 
              } else {
                  // Mode edit
                  $response = Http::withOptions([
                      'headers' => ['Accept' => 'application/json']
                  ])->asJson()
                  ->post('http://localhost/sbwebapp/public/app/im-purchasing/purchasing/bpb/edituse_api', [
                      'connection' => 'TESTING',
                      'nouse' => $first->nouse,
                      'company' => $first->companyinv,
                      'factory' => $first->factoryinv,
                      'isi' => array_values($apiPayload),  
                      'userid' => substr(auth()->user()->userid, 0, 10)
                  ]);
              }
    
              // Log
              if ($response->successful()) {
                  Log::info('API success:', $response->json());
              } else {
                  Log::error('API error', [
                      'status' => $response->status(),
                      'body' => $response->body()
                  ]);
              }
              
              // Success update nouse
              if($response->status() == 200) { 
                  if($response->json()['status'] == 1) {
                      usemateriallst::where('rkhno', $request->rkhno)->update(['nouse' => $response->json()['noUse']]);
                      usematerialhdr::where('rkhno', $request->rkhno)->update(['flagstatus' => 'DISPATCHED']);
                  }
              } else {
                  dd($response->json(), $response->body(), $response->status());
              }

          
          return redirect()->back()->with('success1', 'Data updated successfully');
        
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
