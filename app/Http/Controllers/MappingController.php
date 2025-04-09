<?php

namespace App\Http\Controllers;

use App\Models\Blok;
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
            'routeName' => route('master.mapping.index'),
        ]);
    }
    public function index(Request $request)
    {
        $title = "Daftar Mapping";

        $bloks = DB::table('blok')->where('companycode', '=', session('dropdown_value'))->get();
        $plotting = DB::table('plot')->where('companycode', '=', session('dropdown_value'))->get();

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        $mapping = DB::table('mappingblokplot')->where('companycode', '=', session('dropdown_value'))
            ->orderByRaw('CAST(idblokplot AS UNSIGNED) ASC')
            ->paginate($perPage);

        foreach ($mapping as $index => $item) {
            $item->no = ($mapping->currentPage() - 1) * $mapping->perPage() + $index + 1;
        }
        return view('master.mapping.index', compact('mapping', 'bloks', 'plotting', 'perPage', 'title'));
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
            'plotcodesample' => 'required',
            'blok' => 'required|exists:blok,blok',
            'plot' => 'required|exists:plotting,plotcode',
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
        $exists = DB::table('mappingblokplot')->where('idblokplot', $request->plotcodesample)
            ->where('blok', $request->blok)
            ->where('plot', $request->plotcode)
            ->where('companycode', $request->companycode)
            ->exists();
        $existCode = DB::table('mappingblokplot')->where('idblokplot', $request->plotcodesample)
            ->where('companycode', $request->companycode)
            ->exists();
        $existBody = DB::table('mappingblokplot')->where('blok', $request->blok)
            ->where('plot', $request->plotcode)
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
                'idblokplot' => $request->plotcodesample,
                'blok' => $request->blok,
                'plot' => $request->plotcode,
                'companycode' => session('dropdown_value'),
                'usernm' => Auth::user()->usernm,
                'createdat' => now(),
                'updatedat' => now(),
            ]);
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'newData' => [
                'no' => 'NEW!',
                'plotcodesample' => $request->plotcodesample,
                'blok' => $request->blok,
                'plot' => $request->plotcode,
                'companycode' => $request->companycode,
            ]
        ]);
    }

    public function update(Request $request, $plotcodesample, $blok, $plotcode, $companycode)
    {
        $request->validate($this->requestValidated());

        $exists = DB::table('mappingblokplot')->where('idblokplot', $request->plotcodesample)
            ->where('blok', $blok)
            ->where('plot', $plotcode)
            ->where('companycode', $companycode)
            ->where('idblokplot', '!=', $plotcodesample)
            ->exists();
        $existCode = DB::table('mappingblokplot')->where('idblokplot', $request->plotcodesample)
            ->where('companycode', $request->companycode)
            ->where('idblokplot', '!=', $plotcodesample)
            ->exists();
        $existBody = DB::table('mappingblokplot')->where('blok', $request->blok)
            ->where('plot', $request->plotcode)
            ->where('companycode', $request->companycode)
            ->where('idblokplot', '!=', $plotcodesample)
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

        DB::transaction(function () use ($request, $plotcodesample, $blok, $plotcode, $companycode) {
            DB::table('mappingblokplot')->where('idblokplot', $plotcodesample)
                ->where('blok', $blok)
                ->where('plot', $plotcode)
                ->where('companycode', $companycode)
                ->update([
                    'idblokplot' => $request->plotcodesample,
                    'blok' => $request->blok,
                    'plot' => $request->plotcode,
                    'companycode' => session('dropdown_value'),
                    'usernm' => Auth::user()->usernm,
                    'updatedat' => now(),
                ]);
        });

        return redirect()->route('master.mapping.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function destroy($plotcodesample, $blok, $plotcode, $companycode)
    {
        DB::transaction(function () use ($plotcodesample, $blok, $plotcode, $companycode) {
            DB::table('mappingblokplot')->where('idblokplot', $plotcodesample)
                ->where('blok', $blok)
                ->where('plot', $plotcode)
                ->where('companycode', $companycode)
                ->delete();
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
