<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Varietas;
use Illuminate\Support\Facades\Auth;

class VarietasController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)$request->input('perPage', 10);
        $search  = $request->input('search');

        $query = Varietas::query();
        if ($search) {
            $query->where(fn($q) =>
                $q->where('kodevarietas', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
            );
        }

        $varietas = $query
            ->orderBy('kodevarietas')
            ->paginate($perPage)
            ->appends(compact('perPage', 'search'));

        return view('masterdata.varietas.index', [
            'varietas' => $varietas,
            'title'    => 'Data Varietas',
            'navbar'   => 'Master',
            'nav'      => 'Varietas',
            'perPage'  => $perPage,
            'search'   => $search,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kodevarietas' => 'required|string|max:10',
            'description'   => 'required|string|max:255',
        ]);

        if (Varietas::where('kodevarietas', $request->kodevarietas)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['kodevarietas' => 'Duplicate Entry, Kode sudah ada']);
        }

        Varietas::create([
            'kodevarietas' => $request->kodevarietas,
            'description'   => $request->description,
            'inputby'      => Auth::user()->userid,
            'createdat'    => now(),
        ]);

        return back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $kodevarietas)
    {
        $varietas = Varietas::findOrFail($kodevarietas);

        $validated = $request->validate([
            'kodevarietas' => 'required|string|max:10',
            'description'   => 'required|string|max:255',
        ]);

        // cek duplicate jika kode diubah
        if ($validated['kodevarietas'] !== $varietas->kodevarietas &&
            Varietas::where('kodevarietas', $validated['kodevarietas'])->exists()) {
            return back()
                ->withInput()
                ->withErrors(['kodevarietas' => 'Duplicate Entry, Kode sudah ada']);
        }

        $varietas->update([
            'kodevarietas' => $validated['kodevarietas'],
            'description'   => $validated['description'],
            'updateby'     => Auth::user()->userid,
            'updatedat'    => now(),
        ]);

        return back()->with('success', 'Data berhasil di-update.');
    }

    public function destroy($kodevarietas)
    {
        Varietas::where('kodevarietas', $kodevarietas)->delete();
        return back()->with('success', 'Data berhasil di-hapus.');
    }
}
