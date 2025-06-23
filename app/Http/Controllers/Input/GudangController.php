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

    public function index(Request $request)
    {
        $title = "Gudang";

        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'required|integer|min:1']);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        //$activities = Activity::with('group')->orderBy('activitycode', 'asc')->paginate($perPage);
        //$activityGroup = ActivityGroup::get();

        //foreach ($activities as $index => $item) {
        //    $item->no = ($activities->currentPage() - 1) * $activities->perPage() + $index + 1;
        //}
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

    public function submit(Request $request)
    {   
        $usematerialhdr = new usematerialhdr;
        $usemateriallst = new usemateriallst;
        $dosage = new HerbisidaDosage;
        $details = $usematerialhdr->selectuse(session('companycode'), $request->rkhno,1)->get();
        $exists = usemateriallst::where('rkhno', $request->rkhno)->whereNotNull('nouse')->where('nouse', '!=', '')->exists();
        $first = $details->first();
  
        if($details->whereNotNull('nouse')->count()<1){
            
        $isi = collect();
        if( $exists == FALSE ){
        if(!empty($details)){ 
        $testisi = collect([
            (object)[
                'CompCodeTerima' => 'GSB',
                'FactoryTerima'  => 'ATK',
                'ItemGrup'       => '02',
                'CompItemcode'   => '000155',
                'prunit'         => 'BH',
                'itemprice'      => '3100000',
                'currcode'       => 'IDR',
                'itemnote'       => 'U/ TEST MESIN TAPIOKA PENGEMASAN TEST',
                'qtybpb'         => '2',
                'Keterangan'     => '',
                'vehiclenumber'  => '2',
                'flagstatus'     => 'POSTED'
            ]
          ]);
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

        DB::transaction(function() use ($request, $first, $groupedDetails, $herbisidaitem, $isi) {
        
        usemateriallst::where('rkhno', $first->rkhno)->delete();
        $insertData = [];
        

        foreach($groupedDetails as $groupId => $items){ 
            foreach ($request->itemcode[$groupId] as $index => $itemcode) { 
                
                $dosage = $request->dosage[$groupId][$index]; 
                $qty = $dosage*$items->totalLuas;
                $unit = $request->unit[$groupId][$index];  
                $itemname = $herbisidaitem->where('itemcode', $itemcode)->first()->itemname;
                $qtyretur = $request->qtyretur[$groupId][$index];

                $insertData[] = [
                    'companycode' => session('companycode'),
                    'rkhno' => $request->rkhno,
                    'itemcode' => $itemcode,
                    'qty' => $qty,
                    'unit' => $request->unit,
                    'qtyretur' => $qtyretur,
                    'itemname' => $itemname,
                    'dosageperha' => $dosage
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
            
        if ($response->successful()) {
            Log::info('API success:', $response->json());
        } else {
            Log::error('API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
            
            if($response->status()==200){ 
                if($response->json()['status']==1){
                usemateriallst::where('rkhno', $request->rkhno)->update(['nouse' => $response->json()['noUse']]);
                usematerialhdr::where('rkhno', $request->rkhno)->update(['flagstatus' => 'SUBMITTED']);
                }
            }else{
                return redirect()->back()->with('success1', 'Data updated successfully');
            }
        }
        }
        // ->withErrors(['activitycode' => 'Duplicate Entry, kombinasi kode sudah ada']);
        return redirect()->back()->with('success1', 'Data updated successfully');
        }else{ return redirect()->back()->with('warning', 'Data Use Sudah Ada!'); }//tutup cek nouse < 1
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
