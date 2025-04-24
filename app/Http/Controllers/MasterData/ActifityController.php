<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;

use App\Models\Actifity;
use App\Models\ActifityGroup;
use App\Models\Blok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class ActifityController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'Aktifitas',
            'routeName' => route('master.aktifitas.index'),
        ]);
    }

    public function index(Request $request)
    {
        $title = "Daftar Aktifitas";

        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'required|integer|min:1']);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        $actifities = Actifity::with('group')->orderBy('actifitycode', 'asc')->paginate($perPage);
        $actifityGroup = ActifityGroup::get();

        foreach ($actifities as $index => $item) {
            $item->no = ($actifities->currentPage() - 1) * $actifities->perPage() + $index + 1;
        }
        return view('master.actifity.index')->with([
            'title'         => 'Daftar Aktifitas',
            'perPage'       => $perPage,
            'actifities'    => $actifities,
            'actifityGroup' => $actifityGroup
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
          'actifitycode' => 'required',
          'actifitygroup' => 'required|exists:actifitygroup,actifitygroup',
          'actifityname' => 'required',
          'description' => 'max:150',
          'var.*'       => 'required',
          'satuan.*'    => 'required'
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('actifitycode')->where('actifitycode', $request->actifitycode)->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah ada, silahkan coba dengan data yang berbeda.'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            DB::table('actifitycode')->insert([
                'actifitycode'  => $request->actifitycode,
                'actifitygroup' => $request->actifitgroup,
                'actifityname'  => $request->actifityname,
                'description'   => $request->description,
                'jumlahvar'     => count($request->var),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'newData' => [
                'no' => 'NEW!',
                'blok' => $request->blok,
                'companycode' => $request->companycode,
            ]
        ]);
    }

    public function update(Request $request, $blok, $companycode)
    {
        $request->validate($this->requestValidated());

        $exists = DB::table('blok')->where('blok', $request->blok)
            ->where('companycode', $companycode)
            ->where('blok', '!=', $blok)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Data sudah ada, silakan gunakan nama lain.')
                ->withInput();
        }

        DB::transaction(function () use ($request, $blok, $companycode) {

            DB::table('blok')->where('blok', $blok)
                ->where('companycode', $companycode)
                ->update([
                    'blok' => $request->blok,
                    'companycode' => session('companycode'),
                    'inputby' => Auth::user()->userid,
                    'updatedat' => now()
                ]);
        });

        return redirect()->route('master.blok.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function destroy($blok, $companycode)
    {
        DB::transaction(function () use ($blok, $companycode) {
            DB::table('blok')->where('blok', $blok)->where('companycode', $companycode)->delete();
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
