<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Herbisida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class HerbisidaController extends Controller
{
    public function __construct()
    {
        // Menyebarkan data untuk navbar dan lainnya
        View::share([
            'navbar' => 'Master',
            'nav' => 'Herbisida',
            'routeName' => route('master.herbisida.index'),
        ]);
    }

    public function index(Request $request)
    {
        $title = "Daftar Herbisida";
        $permissions = json_decode(Auth::user()->permissions, true);
        $isAdmin = in_array('Admin', $permissions);

        $perPage = $request->session()->get('perPage', 10);

        $herbisida = Herbisida::paginate($perPage);

        return view('master.herbisida.index', compact('herbisida', 'perPage', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'itemcode' => 'required|max:10',
            'itemname' => 'required|max:50',
            'measure' => 'required|max:10',
            'dosageperha' => 'required|numeric',
            'company_code' => 'required',
        ]);

        $exists = Herbisida::where('itemcode', $request->itemcode)->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah ada, silahkan coba dengan data yang berbeda.'
            ], 422);
        }

        Herbisida::create([
            'itemcode' => $request->itemcode,
            'itemname' => $request->itemname,
            'measure' => $request->measure,
            'dosageperha' => $request->dosageperha,
            'company_code' => $request->company_code,
            'timestamp' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
        ]);
    }

    public function update(Request $request, $itemcode)
    {
        $request->validate([
            'itemname' => 'required|max:50',
            'measure' => 'required|max:10',
            'dosageperha' => 'required|numeric',
            'company_code' => 'required',
        ]);

        $herbisida = Herbisida::findOrFail($itemcode);
        $herbisida->update([
            'itemname' => $request->itemname,
            'measure' => $request->measure,
            'dosageperha' => $request->dosageperha,
            'company_code' => $request->company_code,
        ]);

        return redirect()->route('master.herbisida.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function destroy($itemcode)
    {
        $herbisida = Herbisida::findOrFail($itemcode);
        $herbisida->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
