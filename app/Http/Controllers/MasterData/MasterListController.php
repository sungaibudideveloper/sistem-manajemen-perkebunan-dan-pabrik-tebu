<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\Masterlist;

class MasterListController extends Controller
{
    
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $companycode = Session::get('companycode');
    
        $query = Masterlist::where('companycode', $companycode);
    
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('plot', 'like', "%{$search}%")
                  ->orWhere('blok', 'like', "%{$search}%")
                  ->orWhere('batchno', 'like', "%{$search}%")
                  ->orWhere('kodestatus', 'like', "%{$search}%")
                  ->orWhere('kodevarietas', 'like', "%{$search}%");
            });
        }

        $masterlist = $query
            ->orderBy('plot')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);
    
        return view('master.masterlist.index', [
            'masterlist' => $masterlist,
            'title'     => 'Data Master List',
            'navbar'    => 'Master',
            'nav'       => 'Master List',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }

    public function store(Request $request)
    {
        $companycode = Session::get('companycode');
        
        $request->validate([
            'plot' => 'required|string|max:5',
            'blok' => 'nullable|string|max:2',
            'batchno' => 'required|string|max:20',
            'batchdate' => 'nullable|date',
            'batcharea' => 'nullable|numeric|min:0',
            'tanggalulangtahun' => 'nullable|date',
            'kodevarietas' => 'nullable|string|max:10',
            'kodestatus' => 'nullable|string|max:3',
            'cyclecount' => 'nullable|integer|min:0',
            'jaraktanam' => 'nullable|integer|min:0',
            'lastactivity' => 'nullable|string|max:100',
            'tanggalpanenpc' => 'nullable|date',
            'tanggalpanenrc1' => 'nullable|date',
            'tanggalpanenrc2' => 'nullable|date',
            'tanggalpanenrc3' => 'nullable|date',
        ]);

        $exists = Masterlist::where('companycode', $companycode)
            ->where('plot', $request->plot)
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'plot' => 'Duplicate Entry, Plot already exists'
                ]);
        }

        Masterlist::create([
            'companycode' => $companycode,
            'plot' => $request->input('plot'),
            'blok' => $request->input('blok'),
            'batchno' => $request->input('batchno'),
            'batchdate' => $request->input('batchdate'),
            'batcharea' => $request->input('batcharea'),
            'tanggalulangtahun' => $request->input('tanggalulangtahun'),
            'kodevarietas' => $request->input('kodevarietas'),
            'kodestatus' => $request->input('kodestatus'),
            'cyclecount' => $request->input('cyclecount', 0),
            'jaraktanam' => $request->input('jaraktanam'),
            'lastactivity' => $request->input('lastactivity'),
            'tanggalpanenpc' => $request->input('tanggalpanenpc'),
            'tanggalpanenrc1' => $request->input('tanggalpanenrc1'),
            'tanggalpanenrc2' => $request->input('tanggalpanenrc2'),
            'tanggalpanenrc3' => $request->input('tanggalpanenrc3'),
            'isactive' => 1,
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $plot)
    {   
        $sessionCompanycode = Session::get('companycode');
        
        // Security check - ensure user can only edit their company data
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }
        
        $masterlist = Masterlist::where([
            ['companycode', $companycode],
            ['plot', $plot]
        ])->firstOrFail();

        $validated = $request->validate([
            'plot' => 'required|string|max:5',
            'blok' => 'nullable|string|max:2',
            'batchno' => 'required|string|max:20',
            'batchdate' => 'nullable|date',
            'batcharea' => 'nullable|numeric|min:0',
            'tanggalulangtahun' => 'nullable|date',
            'kodevarietas' => 'nullable|string|max:10',
            'kodestatus' => 'nullable|string|max:3',
            'cyclecount' => 'nullable|integer|min:0',
            'jaraktanam' => 'nullable|integer|min:0',
            'lastactivity' => 'nullable|string|max:100',
            'tanggalpanenpc' => 'nullable|date',
            'tanggalpanenrc1' => 'nullable|date',
            'tanggalpanenrc2' => 'nullable|date',
            'tanggalpanenrc3' => 'nullable|date',
        ]);
        
        // Check duplicate if plot changed
        if ($request->plot !== $masterlist->plot) {
            $exists = Masterlist::where('companycode', $companycode)
                ->where('plot', $request->plot)
                ->exists();
    
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'plot' => 'Duplicate Entry, Plot already exists'
                    ]);
            }
        }
        
        Masterlist::where('companycode', $companycode)
             ->where('plot', $plot)
             ->update([
                'companycode' => $companycode,
                'plot' => $validated['plot'],
                'blok' => $validated['blok'],
                'batchno' => $validated['batchno'],
                'batchdate' => $validated['batchdate'],
                'batcharea' => $validated['batcharea'],
                'tanggalulangtahun' => $validated['tanggalulangtahun'],
                'kodevarietas' => $validated['kodevarietas'],
                'kodestatus' => $validated['kodestatus'],
                'cyclecount' => $validated['cyclecount'],
                'jaraktanam' => $validated['jaraktanam'],
                'lastactivity' => $validated['lastactivity'],
                'tanggalpanenpc' => $validated['tanggalpanenpc'],
                'tanggalpanenrc1' => $validated['tanggalpanenrc1'],
                'tanggalpanenrc2' => $validated['tanggalpanenrc2'],
                'tanggalpanenrc3' => $validated['tanggalpanenrc3'],
             ]);
    
        return redirect()->back()->with('success', 'Data berhasil di‑update.');
    }

    public function destroy(Request $request, $companycode, $plot)
    {
        $sessionCompanycode = Session::get('companycode');
        
        // Security check - ensure user can only delete their company data
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        Masterlist::where([
            ['companycode', $companycode],
            ['plot', $plot]
        ])->delete();

        return redirect()->back()->with('success','Data berhasil di‑hapus.');
    }
}