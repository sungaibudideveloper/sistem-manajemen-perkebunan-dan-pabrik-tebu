<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;

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
            'routeName' => route('masterdata.blok.index'),
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
        $blok = DB::table('blok')->where('companycode', '=', session('companycode'))
            ->orderBy('blok', 'asc')->paginate($perPage);

        foreach ($blok as $index => $item) {
            $item->no = ($blok->currentPage() - 1) * $blok->perPage() + $index + 1;
        }

        return view('masterdata.blok.index', compact('blok', 'perPage', 'title'));
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
            'blok' => 'required|max:2',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('blok')->where('blok', $request->blok)
            ->where('companycode', $request->companycode)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah ada, silahkan coba dengan data yang berbeda.'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            DB::table('blok')->insert([
                'blok' => $request->blok,
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

        return redirect()->route('masterdata.blok.index')
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
