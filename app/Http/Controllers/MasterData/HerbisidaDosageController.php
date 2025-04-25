<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\HerbisidaDosage;

class HerbisidaDosageController extends Controller
{
    
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
    
        $qb = DB::table('herbisidadosage as d')
        ->join('herbisida as h', function($join){
            $join->on('d.companycode', '=', 'h.companycode')
                 ->on('d.itemcode',    '=', 'h.itemcode');
        })
        ->select('d.*', 'h.itemname');

        if ($search) {
            $qb->where(function($q) use ($search) {
                $q->where('d.activitycode', 'like', "%{$search}%")
                ->orWhere('d.itemcode',     'like', "%{$search}%")
                ->orWhere('d.companycode',  'like', "%{$search}%");
            });
        }

        $herbisidaDosages = $qb
            ->orderBy('d.companycode')
            ->orderBy('d.activitycode')
            ->paginate($perPage)
            ->appends(compact('perPage','search'));

        return view('master.herbisidadosage.index', [
            'herbisidaDosages' => $herbisidaDosages,
            'perPage'          => $perPage,
            'search'           => $search,
            'title'            => 'Data Dosis Herbisida',
            'navbar'           => 'Master',
            'nav'              => 'Dosis Herbisida',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'companycode' => 'required|string|max:4',
            'activitycode' => 'required|string|max:50',
            'itemcode' => 'required|string|max:30',
            'time' => 'required|string|max:50',
            'description' => 'nullable|string|max:100',
            'totaldosage' => 'required|numeric',
            'dosageunit' => 'required|string|max:5',
        ]);

        $exists = HerbisidaDosage::where('companycode', $request->companycode)
            ->where('activitycode', $request->activitycode)
            ->where('itemcode', $request->itemcode)
            ->exists();
        if ($exists) {
        return redirect()->back()
            ->withInput()
            ->withErrors([
                'activitycode' => 'Duplicate Entry, Data already exists'
            ]);
        }

        HerbisidaDosage::create([
            'companycode' => $request->input('companycode'),
            'activitycode' => $request->input('activitycode'),
            'itemcode' => $request->input('itemcode'),
            'time' => $request->input('time'),
            'description' => $request->input('description'),
            'totaldosage' => $request->input('totaldosage'),
            'dosageunit' => $request->input('dosageunit'),
            'inputby'      => Auth::user()->userid,
            'createdat'    => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $activitycode, $itemcode)
    {
        $dosage = HerbisidaDosage::where([
            ['companycode', $companycode],
            ['activitycode', $activitycode],
            ['itemcode', $itemcode]
        ])->firstOrFail();

        $validated= $request->validate([
            'companycode'  => 'required|string|max:4',
            'activitycode' => 'required|string|max:50',
            'itemcode'     => 'required|string|max:30',
            'time'         => 'required|string|max:50',
            'description'  => 'nullable|string|max:100',
            'totaldosage'  => 'required|numeric',
            'dosageunit'   => 'required|string|max:5',
        ]);

        // Check if the companycode, activitycode, or itemcode has changed
        if ($request->companycode !== $dosage->companycode ||
            $request->activitycode !== $dosage->activitycode ||
            $request->itemcode !== $dosage->itemcode) {
            
            $exists = HerbisidaDosage::where('companycode',  $request->companycode)
                ->where('activitycode', $request->activitycode)
                ->where('itemcode', $request->itemcode)
                ->exists();
    
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'activitycode' => 'Duplicate Entry, Data already exists'
                    ]);
            }
        }
        // harus ditulis ulang semua agar mengedit baris yang tepat tidak bisa pake $dosage
        HerbisidaDosage::where([
            ['companycode',   $companycode],
            ['activitycode',  $activitycode],
            ['itemcode',      $itemcode],
        ])->update([
            'companycode'   => $validated['companycode'],
            'activitycode'  => $validated['activitycode'],
            'itemcode'      => $validated['itemcode'],
            'time'          => $validated['time'],
            'description'   => $validated['description'],
            'totaldosage'   => $validated['totaldosage'],
            'dosageunit'    => $validated['dosageunit'],
            'updateby'      => Auth::user()->userid,
            'updatedat'     => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil diupdate.');
    }
    

    public function destroy(Request $request, $companycode, $activitycode, $itemcode)
{
    HerbisidaDosage::where([
        ['companycode', $companycode],
        ['activitycode', $activitycode],
        ['itemcode', $itemcode]
    ])->delete();

    return redirect()->back()->with('success','Data berhasil dihapus.');
}
}
