<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Jabatan;

class JabatanController extends Controller
{
    
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
    
        $query = Jabatan::query();
    
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('idjabatan', 'like', "%{$search}%")
                  ->orWhere('namajabatan', 'like', "%{$search}%");
            });
        }

        $jabatan = $query
            ->orderBy('idjabatan')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);
    
        return view('master.jabatan.index', [
            'jabatan'   => $jabatan,
            'title'     => 'Data Jabatan',
            'navbar'    => 'Master',
            'nav'       => 'Jabatan',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'namajabatan' => 'required|string|max:30',
        ]);
            
        Jabatan::create([
            'namajabatan' => $request->namajabatan,
        ]);

        return redirect()->back()->with('success', 'Data berhasil dibuat.');
    }

    public function update(Request $request, $idjabatan)
    {   
        $jabatan = Jabatan::findOrFail($idjabatan);
    
        $request->validate([
            'namajabatan' => 'required|string|max:30',
        ]);
   
        $jabatan->update([
            'namajabatan' => $request->namajabatan,
        ]);
    
        return redirect()->back()->with('success', 'Data berhasil di-update.');
    }

    public function destroy(Request $request, $idjabatan)
{

    Jabatan::where([
        ['idjabatan', $idjabatan]
    ])->delete();

    return redirect()->back()->with('success','Data berhasil di‑hapus.');
}
}
