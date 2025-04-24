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
            'idjabatan' => 'required|int|max:999',
            'namajabatan' => 'required|string|max:30',
        ]);

        $exists = Jabatan::where('idjabatan', $request->idjabatan)
            ->exists();

        if ($exists) {
        return redirect()->back()
            ->withInput()
            ->withErrors([
                'idjabatan' => 'Duplicate Entry, Item Code already exists'
            ]);
        }

        Jabatan::create([
            'idjabatan' => $request->input('idjabatan'),
            'namajabatan' => $request->input('namajabatan'),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $idjabatan)
    {   
        $jabatan = Jabatan::where([
            ['idjabatan', $idjabatan]
        ])->firstOrFail();

        $validated=$request->validate([
            'idjabatan' => 'required|int|max:999',
            'namajabatan' => 'required|string|max:30',
        ]);
        
        if (
            (int)$request->idjabatan !== $jabatan->idjabatan){ //Dsini pake (int) karena data dari form adalah string

            $exists = Jabatan::where('idjabatan',  $request->idjabatan)
                ->exists();
    
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'idjabatan' => 'Duplicate Entry, ID already exists'
                    ]);
            }
        }
        
        Jabatan::where([
            ['idjabatan', $idjabatan]
        ])->update($validated);

    
        return redirect()->back()->with('success', 'Data berhasil di‑update.');
    }

    public function destroy(Request $request, $idjabatan)
{

    Jabatan::where([
        ['idjabatan', $idjabatan]
    ])->delete();

    return redirect()->back()->with('success','Data berhasil di‑hapus.');
}
}
