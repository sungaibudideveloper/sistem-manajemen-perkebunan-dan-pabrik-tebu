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
    {   $usematerialhdr = new usematerialhdr; 
        $usehdr= $usematerialhdr->selectuse(session('companycode'));
        
        $title = "Gudang";

        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'required|integer|min:1']);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $usehdr = usematerialhdr::where('companycode', session('companycode'))->orderBy('createdat', 'desc')->paginate($perPage);
        
        return view('input.gudang.home')->with([
            'title'         => 'Gudang',
            'usehdr'        => $usehdr,
            'perPage'       => $perPage
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
        'd.herbisidagroupid',
        'd.itemcode',
        'd.dosageperha',
        'd.dosageunit',
        'h.itemname',
        'h.measure'
        )
        ->get();
            
        $details = $usematerialhdr->selectuse(session('companycode'), $request->rkhno,1)->get();
        
        $groupIds = $details->pluck('herbisidagroupid')->unique();
        $lst = usemateriallst::where('rkhno', $request->rkhno)->get();
        //$joinlst = $usemateriallst->joinlst($request->rkhno);

        // $header
        $title = "Gudang";
          
        return view('input.gudang.detail')->with([
            'title'         => 'Gudang',
            'details'       => $details,
            'dosage'        => $dosage,
            'lst'       => $lst,
            'itemlist'      => $itemlist
        ]);
    }

    public function retur(Request $request)
    {
        $usematerialhdr = new usematerialhdr;
        $usemateriallst = new usemateriallst;
        $header = $usematerialhdr->selectuse(session('companycode'), $request->rkhno,1)->get();
        $hfirst = $header->where('herbisidagroupid',$request->herbisidagroupid)->first();
        
        $details = usemateriallst::where('rkhno', $hfirst->rkhno)->where('herbisidagroupid', $hfirst->herbisidagroupid)->where('itemcode', $request->itemcode);
        $first = $details->first();

        if(empty($first->qtyretur)){
            return redirect()->back()->with('error', 'Cant Retur! Qty Retur Empty');
        } 
        
        if($first->qtyretur>$first->qty){
            return redirect()->back()->with('error', 'Cant Retur! Qty Retur > Qty Kirim');
        }
        if( strtoupper($first->flagstatus) == 'COMPLETED' ){
            return redirect()->back()->with('error', 'Items Already Completed');
        }

        if($first->noretur != null){
            return redirect()->back()->with('error', 'Cant Retur! No Retur Not Empty');
        }


        $isi = collect();
        $isi->push((object)[
            'CompCodeTerima' => $hfirst->companyinv, 
            'FactoryTerima'  => $hfirst->factoryinv,
            'ItemGrup'       => substr($first->itemcode, 0, 1),
            'CompItemcode'   => substr($first->itemcode, 1),
            'prunit'         => $first->unit,
            'itemprice'      =>  0,
            'currcode'       => 'IDR',
            'itemnote'       => $hfirst->herbisidagroupname,
            'qtybpb'         => $first->qtyretur,
            'Keterangan'     => $hfirst->herbisidagroupname.' - '.$hfirst->mandorname ?? '',  
            'vehiclenumber'  => '',
            'flagstatus'     => $hfirst->flagstatus
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
                ->where('herbisidagroupid',$hfirst->herbisidagroupid)->update(['noretur' => $response->json()['noretur']]);
                }
            }else{
                dd($response->json(), $response->body(), $response->status());
            }

        // $usemateriallst->where('itemcode', $request->itemnumber)
        // ->where('rkhno', $request->rkhno)
        // ->where('herbisidagroupid', $request->herbisidagroupid)
        // ->update(['noretur' => 'R-000123']);

        return redirect()->back()->with('success1', 'Sukses Membuat Dokumen Retur '. $response->json()['noretur']);
    }

    public function submit(Request $request)
    {   
        $usematerialhdr = new usematerialhdr;
        $usemateriallst = new usemateriallst;
        $dosage = new HerbisidaDosage;
        $details = $usematerialhdr->selectuse(session('companycode'), $request->rkhno,1)->get();
        $exists = usemateriallst::where('rkhno', $request->rkhno);
        
        $first = $details->first();
        if(strtoupper($first->flagstatus) == 'RECEIVED' || strtoupper($first->flagstatus) == 'COMPLETED' ){
            return redirect()->back()->with('error', 'Cant Edit! Items Already Received');
        }

        $isi = collect();
        if(!empty($details)){ 
          $groupIds = $details->pluck('herbisidagroupid')->unique();
          $dosage = HerbisidaDosage::whereIn('herbisidagroupid', $groupIds)->get();

          $groupedDetails = $details->groupBy('herbisidagroupid')->map(function ($group) {
            $totalLuas = $group->sum('luasarea');
            $firstItem = $group->first();

            return (object)[
                'CompCodeTerima' => $firstItem->companyinv,
                'FactoryTerima' => $firstItem->factoryinv,
                'herbisidagroupid' => $firstItem->herbisidagroupid,
                'herbisidagroupname' => $firstItem->herbisidagroupname,
                'mandorname' => $firstItem->mandorname,
                'flagstatus' => $firstItem->flagstatus,
                'totalLuas' => $totalLuas
            ];
        });
        
        $herbisidaitem = Herbisida::where('companycode',session('companycode'))->get();
        // menghapus dan isi detail
        DB::transaction(function() use ($request, $first, $groupedDetails, $herbisidaitem, $isi, $exists, $details) {
        $isidetail = $exists->get();
        usemateriallst::where('rkhno', $first->rkhno)->delete();
        $insertData = [];

        foreach($groupedDetails as $groupId => $items){ 
            foreach ($request->itemcode[$groupId] as $index => $itemcode) { 
                
                $dosage = $request->dosage[$groupId][$index]; 
                $qty = $dosage*$items->totalLuas;
                $unit = $request->unit[$groupId][$index];  
                $itemname = $herbisidaitem->where('itemcode', $itemcode)->first()->itemname; 
                // apabila item berubah maka qty retur akan 0 , perlu dijaga di atas agar tidak boleh rubah item saat qty retur > 0
                // sama seperti if null maka qtyretur 0 , namun lebih rapih
                $qtyretur = $isidetail->where('itemcode', $itemcode)
                     ->where('herbisidagroupid', $groupId)
                     ->first()?->qtyretur ?? 0;
                
                
                $insertData[] = [
                    'companycode' => session('companycode'),
                    'rkhno' => $request->rkhno,
                    'itemcode' => $itemcode,
                    'qty' => $qty,
                    'unit' => $unit,
                    'qtyretur' => $qtyretur,
                    'itemname' => $itemname,
                    'dosageperha' => $dosage,
                    'herbisidagroupid' =>$groupId,
                    'nouse' => $exists->first() ? $exists->first()->nouse : null
                ];
                
                $isi->push((object)[
                    'CompCodeTerima' => $items->CompCodeTerima, 
                    'FactoryTerima'  => $items->FactoryTerima,
                    'ItemGrup'       => substr($itemcode, 0, 1),
                    'CompItemcode'   => substr($itemcode, 1),
                    'prunit'         => $unit,
                    'itemprice'      =>  0,
                    'currcode'       => 'IDR',
                    'itemnote'       => $items->herbisidagroupname,
                    'qtybpb'         => $dosage*floatval($items->totalLuas),
                    'Keterangan'     => $items->herbisidagroupname.' - '.$items->mandorname ?? '',  
                    'vehiclenumber'  => '',
                    'flagstatus'     => $items->flagstatus
                ]);

            }
          }
          
          // Bulk insert
            if (!empty($insertData)) {
                usemateriallst::insert($insertData);
            }
          });
         

// Group by composite key and sum quantities
$grouped = [];
$keyMap = [];
foreach($isi as $item) {
    $key = $item->CompCodeTerima . '|' . $item->FactoryTerima . '|' . $item->ItemGrup . $item->CompItemcode;
    if (isset($keyMap[$key])) {
        // Sum to existing item
        $index = $keyMap[$key];
        $grouped[$index]->qtybpb += floatval($item->qtybpb);
    } else {
        // Add new item
        $grouped[] = clone $item;
        $grouped[count($grouped)-1]->qtybpb = floatval($item->qtybpb);
        $keyMap[$key] = count($grouped) - 1;
    }
}
$isi = collect(array_values($grouped));
        
        //filter untuk insert atau edit
        if($details->whereNotNull('nouse')->count()<1){  
        //mode insert
            $response = Http::withOptions([
                'headers' => ['Accept' => 'application/json']
            ])->asJson()
            ->post('http://localhost/sbwebapp/public/app/im-purchasing/purchasing/bpb/use_api', [
                'connection' => 'TESTING',
                'company' => $first->companyinv,
                'factory' => $first->factoryinv,
                'isi' => $isi,  
                'userid' => auth::user()->userid 
            ]); 
        //mode edit
        }else{
            $response = Http::withOptions([
                'headers' => ['Accept' => 'application/json']
            ])->asJson()
            ->post('http://localhost/sbwebapp/public/app/im-purchasing/purchasing/bpb/edituse_api', [
                'connection' => 'TESTING',
                'nouse' => $first->nouse,
                'company' => $first->companyinv,
                'factory' => $first->factoryinv,
                'isi' => $isi,  
                'userid' => auth::user()->userid 
            ]);
        }

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
                usemateriallst::where('rkhno', $request->rkhno)->update(['nouse' => $response->json()['noUse']]);
                usematerialhdr::where('rkhno', $request->rkhno)->update(['flagstatus' => 'SUBMITTED']);
                }
            }else{
                dd($response->json(), $response->body(), $response->status());
                return redirect()->back()->with('success1', 'Data updated successfully');
            }
        }
        
        // ->withErrors(['activitycode' => 'Duplicate Entry, kombinasi kode sudah ada']);
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
