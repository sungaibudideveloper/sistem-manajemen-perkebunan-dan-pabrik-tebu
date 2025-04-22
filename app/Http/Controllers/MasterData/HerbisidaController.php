<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Herbisida;

class HerbisidaController extends Controller
{
    
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
    
        $query = Herbisida::query();
    
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('itemcode', 'like', "%{$search}%")
                  ->orWhere('companycode',     'like', "%{$search}%")
                  ->orWhere('itemname',  'like', "%{$search}%");
            });
        }

        $herbisida = $query
            ->orderBy('itemcode')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);
    
        return view('master.herbisida.index', [
            'herbisida' => $herbisida,
            'title'     => 'Data Herbisida',
            'navbar'    => 'Master',
            'nav'       => 'Herbisida',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'companycode' => 'required|string|max:4',
            'itemcode' => 'required|string|max:30',
            'itemname' => 'required|string|max:50',
            'measure' => 'required|string|max:10',
            'dosageperha' => 'required|numeric',
        ]);

        $exists = Herbisida::where('companycode', $request->companycode)
            ->where('itemcode', $request->itemcode)
            ->exists();
        if ($exists) {
        return redirect()->back()
            ->withInput()
            ->withErrors([
                'itemcode' => 'Duplicate Entry, Item Code already exists'
            ]);
        }

        Herbisida::create([
            'companycode' => $request->input('companycode'),
            'itemcode' => $request->input('itemcode'),
            'itemname' => $request->input('itemname'),
            'measure' => $request->input('measure'),
            'dosageperha' => $request->input('dosageperha'),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'companycode' => 'required|string|max:4',
            'itemcode' => 'required|string|max:30',
            'itemname' => 'required|string|max:50',
            'measure' => 'required|string|max:10',
            'dosageperha' => 'required|numeric',
        ]);
        
        $herbisida = Herbisida::findOrFail($id);
        if ( /* Jika companycode dan itemcode diubah pada modal edit (request), periksa apakah sudah ada yang sama */
            $request->companycode  !== $herbisida->companycode ||
            $request->itemcode !== $herbisida->itemcode
        ) {
            $exists = Herbisida::where('companycode',  $request->companycode)
                ->where('itemcode', $request->itemcode)
                ->exists();
    
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'itemcode' => 'Duplicate Entry, Item Code already exists'
                    ]);
            }
        }
        
        $herbisida = Herbisida::findOrFail($id);
        $herbisida->update($request->only([
            'companycode',
            'itemcode',
            'itemname',
            'measure',
            'dosageperha',
        ]));
    
        return redirect()->back()->with('success', 'Data berhasil di‑update.');
    }

    public function destroy(Request $request, string $itemcode)
{
    $company  = $request->input('companycode');

    Herbisida::where('companycode', $company)
        ->where('itemcode', $itemcode)
        ->delete();

    return redirect()->back()->with('success','Data berhasil di‑hapus.');
}
}
