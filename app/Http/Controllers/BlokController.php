<?php

namespace App\Http\Controllers;

use App\Models\Blok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class BlokController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'Blok',
            'routeName' => route('master.blok.index'),
        ]);
    }

    public function index(Request $request)
    {
        $title = "Daftar Blok";

        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'required|integer|min:1']);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        $blok = DB::table('blok')->where('kd_comp', '=', session('dropdown_value'))
            ->orderBy('kd_blok', 'asc')->paginate($perPage);

        foreach ($blok as $index => $item) {
            $item->no = ($blok->currentPage() - 1) * $blok->perPage() + $index + 1;
        }

        return view('master.blok.index', compact('blok', 'perPage', 'title'));
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
            'kd_blok' => 'required|max:2',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('blok')->where('kd_blok', $request->kd_blok)
            ->where('kd_comp', $request->kd_comp)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah ada, silahkan coba dengan data yang berbeda.'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            DB::table('blok')->insert([
                'kd_blok' => $request->kd_blok,
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
                'kd_blok' => $request->kd_blok,
                'kd_comp' => $request->kd_comp,
            ]
        ]);
    }

    public function update(Request $request, $kd_blok, $kd_comp)
    {
        $request->validate($this->requestValidated());

        $exists = DB::table('blok')->where('kd_blok', $request->kd_blok)
            ->where('kd_comp', $kd_comp)
            ->where('kd_blok', '!=', $kd_blok)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Data sudah ada, silakan gunakan nama lain.')
                ->withInput();
        }

        DB::transaction(function () use ($request, $kd_blok, $kd_comp) {

            DB::table('blok')->where('kd_blok', $kd_blok)
                ->where('kd_comp', $kd_comp)
                ->update([
                    'kd_blok' => $request->kd_blok,
                    'kd_comp' => session('dropdown_value'),
                    'usernm' => Auth::user()->usernm,
                    'updated_at' => now()
                ]);
        });

        return redirect()->route('master.blok.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function destroy($kd_blok, $kd_comp)
    {
        DB::transaction(function () use ($kd_blok, $kd_comp) {
            DB::table('blok')->where('kd_blok', $kd_blok)->where('kd_comp', $kd_comp)->delete();
        });
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
