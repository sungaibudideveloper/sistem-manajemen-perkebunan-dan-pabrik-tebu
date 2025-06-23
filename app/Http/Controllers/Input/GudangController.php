<?php

namespace App\Http\Controllers\Input;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

use App\Models\Use_hdr;
use App\Models\Use_lst;
use App\Models\HerbisidaDosage;

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
    {   $Use_hdr = new Use_hdr; 
        $usehdr= $Use_hdr->selectuse(session('companycode'));
        
        $title = "Gudang";

        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'required|integer|min:1']);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $usehdr = Use_hdr::where('companycode', session('companycode'))->orderBy('createdat', 'desc')->paginate($perPage);
        
        return view('input.gudang.home')->with([
            'title'         => 'Gudang',
            'usehdr'        => $usehdr,
            'perPage'       => $perPage
        ]);
    }

    public function detail(Request $request)
    {   
        $Use_hdr = new Use_hdr;
        $dosage = new HerbisidaDosage;
        $details = $Use_hdr->selectuse(session('companycode'), $request->rkhno,1)->get();
        
        //$dosage = HerbisidaDosage::where('herbisidagroupid', $details[0]->herbisidagroupid)->get();

        $groupIds = $details->pluck('herbisidagroupid')->unique();

        $dosage = HerbisidaDosage::whereIn('herbisidagroupid', $groupIds)->get();
        
        // $header
        $title = "Gudang";

        return view('input.gudang.detail')->with([
            'title'         => 'Gudang',
            'details'        => $details,
            'dosage'         => $dosage
        ]);
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
