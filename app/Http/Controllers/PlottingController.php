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
        $plotting = DB::table('plotting')->where('kd_comp', '=', session('dropdown_value'))->orderByRaw("LEFT(kd_plot, 1), CAST(SUBSTRING(kd_plot, 2) AS UNSIGNED)")->paginate($perPage);

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
            'kd_plot' => 'required|max:5',
            'luas_area' => 'required',
            'jarak_tanam' => 'required',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('plotting')->where('kd_plot', $request->kd_plot)
            ->where('kd_comp', $request->kd_comp)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah ada, silahkan coba dengan data yang berbeda.'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            DB::table('plotting')->insert([
                'kd_plot' => $request->kd_plot,
                'luas_area' => $request->luas_area,
                'jarak_tanam' => $request->jarak_tanam,
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
                'kd_plot' => $request->kd_plot,
                'luas_area' => number_format($request->luas_area, 2, '.', ''),
                'jarak_tanam' => $request->jarak_tanam,
                'kd_comp' => $request->kd_comp,
            ]
        ]);
    }

    public function update(Request $request, $kd_plot, $kd_comp)
    {
        $request->validate($this->requestValidated());

        $existingPlot = DB::table('plotting')->where('kd_plot', $request->kd_plot)
            ->where('kd_comp', $kd_comp)
            ->where('kd_plot', '!=', $kd_plot)
            ->exists();

        if ($existingPlot) {
            return redirect()->back()
                ->with('error', 'Data sudah ada, silakan gunakan nama lain.')
                ->withInput();
        }

        DB::transaction(function () use ($request, $kd_plot, $kd_comp) {
            DB::table('plotting')
                ->where('kd_plot', $kd_plot)
                ->where('kd_comp', $kd_comp)
                ->update([
                    'kd_plot' => $request->kd_plot,
                    'luas_area' => $request->luas_area,
                    'jarak_tanam' => $request->jarak_tanam,
                    'kd_comp' => session('dropdown_value'),
                    'usernm' => Auth::user()->usernm,
                    'updated_at' => now(),
                ]);
        });
        return redirect()->route('master.plotting.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function destroy($kd_plot, $kd_comp)
    {
        DB::transaction(function () use ($kd_plot, $kd_comp) {
            DB::table('plotting')->where('kd_plot', $kd_plot)->where('kd_comp', $kd_comp)->delete();
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
