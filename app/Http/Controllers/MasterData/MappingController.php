<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;

use App\Models\MasterData\Blok;
use App\Models\Mapping;
use App\Models\Plotting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class MappingController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'Mapping',
            'routeName' => route('masterdata.mapping.index'),
        ]);
    }
    public function index(Request $request)
    {
        $title = "Daftar Mapping";

        $bloks = DB::table('blok')->where('companycode', '=', session('companycode'))->get();
        $plotting = DB::table('plot')->where('companycode', '=', session('companycode'))->get();

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        $mapping = DB::table('mappingblokplot')->where('companycode', '=', session('companycode'))
            ->orderByRaw('CAST(idblokplot AS UNSIGNED) ASC')
            ->paginate($perPage);

        foreach ($mapping as $index => $item) {
            $item->no = ($mapping->currentPage() - 1) * $mapping->perPage() + $index + 1;
        }
        return view('masterdata.mapping.index', compact('mapping', 'bloks', 'plotting', 'perPage', 'title'));
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
            'idblokplot' => 'required',
            'blok' => 'required|exists:blok,blok',
            'plot' => 'required|exists:plotting,plot',
        ];
    }

    public function getFilteredData(Request $request)
    {
        $companyCode = $request->companycode;
        $bloks = DB::table('blok')->where('companycode', $companyCode)->get();
        $plots = DB::table('plot')->where('companycode', $companyCode)->get();

        return response()->json([
            'bloks' => $bloks,
            'plots' => $plots,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('mappingblokplot')->where('idblokplot', $request->idblokplot)
            ->where('blok', $request->blok)
            ->where('plot', $request->plot)
            ->where('companycode', $request->companycode)
            ->exists();
        $existCode = DB::table('mappingblokplot')->where('idblokplot', $request->idblokplot)
            ->where('companycode', $request->companycode)
            ->exists();
        $existBody = DB::table('mappingblokplot')->where('blok', $request->blok)
            ->where('plot', $request->plot)
            ->where('companycode', $request->companycode)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah ada, silahkan coba dengan data yang berbeda.'
            ], 422);
        } else if ($existCode) {
            return response()->json([
                'success' => false,
                'message' => 'Kode plot sample sudah dipakai, silahkan coba dengan kode yang berbeda.'
            ], 422);
        } else if ($existBody) {
            return response()->json([
                'success' => false,
                'message' => 'Kombinasi blok dan plot sudah ada pada company ' . $request->companycode . '.'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            DB::table('mappingblokplot')->insert([
                'idblokplot' => $request->idblokplot,
                'blok' => $request->blok,
                'plot' => $request->plot,
                'companycode' => session('companycode'),
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updatedat' => now(),
            ]);
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'newData' => [
                'no' => 'NEW!',
                'idblokplot' => $request->idblokplot,
                'blok' => $request->blok,
                'plot' => $request->plot,
                'companycode' => $request->companycode,
            ]
        ]);
    }

    public function update(Request $request, $idblokplot, $blok, $plot, $companycode)
    {
        $request->validate($this->requestValidated());

        $exists = DB::table('mappingblokplot')->where('idblokplot', $request->idblokplot)
            ->where('blok', $blok)
            ->where('plot', $plot)
            ->where('companycode', $companycode)
            ->where('idblokplot', '!=', $idblokplot)
            ->exists();
        $existCode = DB::table('mappingblokplot')->where('idblokplot', $request->idblokplot)
            ->where('companycode', $request->companycode)
            ->where('idblokplot', '!=', $idblokplot)
            ->exists();
        $existBody = DB::table('mappingblokplot')->where('blok', $request->blok)
            ->where('plot', $request->plot)
            ->where('companycode', $request->companycode)
            ->where('idblokplot', '!=', $idblokplot)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Data sudah ada, silakan gunakan nama lain.')
                ->withInput();
        } else if ($existCode) {
            return redirect()->back()
                ->with('error', 'Kode plot sample sudah dipakai, silahkan coba dengan kode yang berbeda.')
                ->withInput();
        } else if ($existBody) {
            return redirect()->back()
                ->with('error', 'Kombinasi blok dan plot sudah ada pada company ' . $request->companycode . '.')
                ->withInput();
        }

        DB::transaction(function () use ($request, $idblokplot, $blok, $plot, $companycode) {
            DB::table('mappingblokplot')->where('idblokplot', $idblokplot)
                ->where('blok', $blok)
                ->where('plot', $plot)
                ->where('companycode', $companycode)
                ->update([
                    'idblokplot' => $request->idblokplot,
                    'blok' => $request->blok,
                    'plot' => $request->plot,
                    'companycode' => session('companycode'),
                    'usernm' => Auth::user()->usernm,
                    'updatedat' => now(),
                ]);
        });

        return redirect()->route('masterdata.mapping.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function destroy($idblokplot, $blok, $plot, $companycode)
    {
        DB::transaction(function () use ($idblokplot, $blok, $plot, $companycode) {
            DB::table('mappingblokplot')->where('idblokplot', $idblokplot)
                ->where('blok', $blok)
                ->where('plot', $plot)
                ->where('companycode', $companycode)
                ->delete();
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
