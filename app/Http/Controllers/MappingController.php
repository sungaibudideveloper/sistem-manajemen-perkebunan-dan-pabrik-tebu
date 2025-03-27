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

        $bloks = DB::table('blok')->where('kd_comp', '=', session('dropdown_value'))->get();
        $plotting = DB::table('plotting')->where('kd_comp', '=', session('dropdown_value'))->get();

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        $mapping = DB::table('mapping')->where('kd_comp', '=', session('dropdown_value'))
            ->orderByRaw('CAST(kd_plotsample AS UNSIGNED) ASC')
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
            'kd_plotsample' => 'required',
            'kd_blok' => 'required|exists:blok,kd_blok',
            'kd_plot' => 'required|exists:plotting,kd_plot',
        ];
    }

    public function getFilteredData(Request $request)
    {
        $companyCode = $request->kd_comp;
        $bloks = DB::table('blok')->where('kd_comp', $companyCode)->get();
        $plots = DB::table('plotting')->where('kd_comp', $companyCode)->get();

        return response()->json([
            'bloks' => $bloks,
            'plots' => $plots,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('mapping')->where('kd_plotsample', $request->kd_plotsample)
            ->where('kd_blok', $request->kd_blok)
            ->where('kd_plot', $request->kd_plot)
            ->where('kd_comp', $request->kd_comp)
            ->exists();
        $existCode = DB::table('mapping')->where('kd_plotsample', $request->kd_plotsample)
            ->where('kd_comp', $request->kd_comp)
            ->exists();
        $existBody = DB::table('mapping')->where('kd_blok', $request->kd_blok)
            ->where('kd_plot', $request->kd_plot)
            ->where('kd_comp', $request->kd_comp)
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
                'message' => 'Kombinasi blok dan plot sudah ada pada company ' . $request->kd_comp . '.'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            DB::table('mapping')->insert([
                'kd_plotsample' => $request->kd_plotsample,
                'kd_blok' => $request->kd_blok,
                'kd_plot' => $request->kd_plot,
                'kd_comp' => session('dropdown_value'),
                'usernm' => Auth::user()->usernm,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'newData' => [
                'no' => 'NEW!',
                'kd_plotsample' => $request->kd_plotsample,
                'kd_blok' => $request->kd_blok,
                'kd_plot' => $request->kd_plot,
                'kd_comp' => $request->kd_comp,
            ]
        ]);
    }

    public function update(Request $request, $kd_plotsample, $kd_blok, $kd_plot, $kd_comp)
    {
        $request->validate($this->requestValidated());

        $exists = DB::table('mapping')->where('kd_plotsample', $request->kd_plotsample)
            ->where('kd_blok', $kd_blok)
            ->where('kd_plot', $kd_plot)
            ->where('kd_comp', $kd_comp)
            ->where('kd_plotsample', '!=', $kd_plotsample)
            ->exists();
        $existCode = DB::table('mapping')->where('kd_plotsample', $request->kd_plotsample)
            ->where('kd_comp', $request->kd_comp)
            ->where('kd_plotsample', '!=', $kd_plotsample)
            ->exists();
        $existBody = DB::table('mapping')->where('kd_blok', $request->kd_blok)
            ->where('kd_plot', $request->kd_plot)
            ->where('kd_comp', $request->kd_comp)
            ->where('kd_plotsample', '!=', $kd_plotsample)
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
                ->with('error', 'Kombinasi blok dan plot sudah ada pada company ' . $request->kd_comp . '.')
                ->withInput();
        }

        DB::transaction(function () use ($request, $kd_plotsample, $kd_blok, $kd_plot, $kd_comp) {
            DB::table('mapping')->where('kd_plotsample', $kd_plotsample)
                ->where('kd_blok', $kd_blok)
                ->where('kd_plot', $kd_plot)
                ->where('kd_comp', $kd_comp)
                ->update([
                    'kd_plotsample' => $request->kd_plotsample,
                    'kd_blok' => $request->kd_blok,
                    'kd_plot' => $request->kd_plot,
                    'kd_comp' => session('dropdown_value'),
                    'usernm' => Auth::user()->usernm,
                    'updated_at' => now(),
                ]);
        });

        return redirect()->route('master.mapping.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function destroy($kd_plotsample, $kd_blok, $kd_plot, $kd_comp)
    {
        DB::transaction(function () use ($kd_plotsample, $kd_blok, $kd_plot, $kd_comp) {
            DB::table('mapping')->where('kd_plotsample', $kd_plotsample)
                ->where('kd_blok', $kd_blok)
                ->where('kd_plot', $kd_plot)
                ->where('kd_comp', $kd_comp)
                ->delete();
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
