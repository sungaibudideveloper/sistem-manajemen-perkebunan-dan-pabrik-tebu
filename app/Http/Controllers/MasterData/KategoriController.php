<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)$request->input('perPage', 10);
        $search  = $request->input('search');

        $query = Kategori::query();
        if ($search) {
            $query->where(fn($q) =>
                $q->where('kodekategori', 'like', "%{$search}%")
                  ->orWhere('namakategori', 'like', "%{$search}%")
            );
        }

        $kategori = $query
            ->orderBy('kodekategori')
            ->paginate($perPage)
            ->appends(compact('perPage', 'search'));

        return view('masterdata.kategori.index', [
            'kategori' => $kategori,
            'title'    => 'Data Kategori',
            'navbar'   => 'Master',
            'nav'      => 'Kategori',
            'perPage'  => $perPage,
            'search'   => $search,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kodekategori' => 'required|string|max:3',
            'namakategori' => 'required|string|max:30',
        ]);

        if (Kategori::where('kodekategori', $request->kodekategori)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['kodekategori' => 'Duplicate Entry, Kode sudah ada']);
        }

        Kategori::create([
            'kodekategori' => $request->kodekategori,
            'namakategori' => $request->namakategori,
            'inputby'      => Auth::user()->userid,
            'createdat'    => now(),
        ]);

        return back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $kodekategori)
    {
        $kategori = Kategori::findOrFail($kodekategori);

        $validated = $request->validate([
            'kodekategori' => 'required|string|max:3',
            'namakategori' => 'required|string|max:30',
        ]);

        // cek duplicate jika kode diubah
        if ($validated['kodekategori'] !== $kategori->kodekategori &&
            Kategori::where('kodekategori', $validated['kodekategori'])->exists()) {
            return back()
                ->withInput()
                ->withErrors(['kodekategori' => 'Duplicate Entry, Kode sudah ada']);
        }

        $kategori->update([
            'kodekategori' => $validated['kodekategori'],
            'namakategori' => $validated['namakategori'],
            'updateby'     => Auth::user()->userid,
            'updatedat'    => now(),
        ]);
    
        return back()->with('success', 'Data berhasil di-update.');
    }

    public function destroy($kodekategori)
    {
        Kategori::where('kodekategori', $kodekategori)->delete();
        return back()->with('success', 'Data berhasil di-hapus.');
    }
}
