<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class PlottingController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'Plotting',
            'routeName' => route('master.plotting.index'),
        ]);
    }

    public function index(Request $request)
    {

        $title = "Daftar Plot";

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        $plotting = DB::table('plot')->where('companycode', '=', session('dropdown_value'))->orderByRaw("LEFT(plot, 1), CAST(SUBSTRING(plot, 2) AS UNSIGNED)")->paginate($perPage);

        foreach ($plotting as $index => $item) {
            $item->no = ($plotting->currentPage() - 1) * $plotting->perPage() + $index + 1;
        }
        return view('master.plotting.index', compact('plotting', 'perPage', 'title'));
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
            'plot' => 'required|max:5',
            'luasarea' => 'required',
            'jaraktanam' => 'required',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('plot')->where('plot', $request->plot)
            ->where('companycode', $request->companycode)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah ada, silahkan coba dengan data yang berbeda.'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            DB::table('plot')->insert([
                'plot' => $request->plot,
                'luasarea' => $request->luasarea,
                'jaraktanam' => $request->jaraktanam,
                'companycode' => session('dropdown_value'),
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
                'plot' => $request->plot,
                'luasarea' => number_format($request->luasarea, 2, '.', ''),
                'jaraktanam' => $request->jaraktanam,
                'companycode' => $request->companycode,
            ]
        ]);
    }

    public function update(Request $request, $plot, $companycode)
    {
        $request->validate($this->requestValidated());

        $existingPlot = DB::table('plot')->where('plot', $request->plot)
            ->where('companycode', $companycode)
            ->where('plot', '!=', $plot)
            ->exists();

        if ($existingPlot) {
            return redirect()->back()
                ->with('error', 'Data sudah ada, silakan gunakan nama lain.')
                ->withInput();
        }

        DB::transaction(function () use ($request, $plot, $companycode) {
            DB::table('plot')
                ->where('plot', $plot)
                ->where('companycode', $companycode)
                ->update([
                    'plot' => $request->plot,
                    'luasarea' => $request->luasarea,
                    'jaraktanam' => $request->jaraktanam,
                    'companycode' => session('dropdown_value'),
                    'inputby' => Auth::user()->userid,
                    'updatedat' => now(),
                ]);
        });
        return redirect()->route('master.plotting.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function destroy($plot, $companycode)
    {
        DB::transaction(function () use ($plot, $companycode) {
            DB::table('plot')->where('plot', $plot)->where('companycode', $companycode)->delete();
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
