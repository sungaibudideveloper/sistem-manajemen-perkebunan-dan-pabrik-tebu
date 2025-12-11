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
        $companycode = session('companycode'); // Filter by session company
    
        $qb = DB::table('herbisidadosage as d')
        ->join('herbisida as h', function($join){
            $join->on('d.companycode', '=', 'h.companycode')
                 ->on('d.itemcode',    '=', 'h.itemcode');
        })
        ->join('herbisidagroup as g', 'd.herbisidagroupid', '=', 'g.herbisidagroupid')
        ->where('d.companycode', $companycode) // Filter by company session
        ->select('d.*', 'h.itemname', 'h.measure', 'g.herbisidagroupid', 'g.herbisidagroupname', 'g.activitycode');

        if ($search) {
            $qb->where(function($q) use ($search) {
                $q->where('d.herbisidagroupid', 'like', "%{$search}%")
                ->orWhere('d.itemcode',     'like', "%{$search}%")
                ->orWhere('h.itemname',     'like', "%{$search}%")
                ->orWhere('g.herbisidagroupname', 'like', "%{$search}%");
            });
        }

        $herbisidaDosages = $qb
            ->orderBy('g.herbisidagroupid')
            ->orderBy('d.itemcode')
            ->paginate($perPage)
            ->appends(compact('perPage','search'));

        return view('masterdata.herbisidadosage.index', [
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
            'herbisidagroupid' => 'required|string|max:4',
            'itemcode' => 'required|string|max:30',
            'dosageperha' => 'required|numeric',
        ]);

        $companycode = session('companycode'); // Use session company

        $exists = HerbisidaDosage::where('companycode', $companycode)
            ->where('herbisidagroupid', intval($request->herbisidagroupid))
            ->where('itemcode', $request->itemcode)
            ->exists();

        if ($exists) {
        return redirect()->back()
            ->withInput()
            ->withErrors([
                'herbisidagroupid' => 'Duplicate Entry, Data already exists'
            ]);
        }
       
        HerbisidaDosage::create([
            'companycode' => $companycode, // Use session company
            'herbisidagroupid' => intval($request->input('herbisidagroupid')),
            'itemcode' => $request->input('itemcode'),
            'dosageperha' => $request->input('dosageperha'),
            'inputby'      => Auth::user()->userid,
            'createdat'    => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $herbisidagroupid, $itemcode)
    {   
        // Ensure user can only update data for their session company
        if ($companycode !== session('companycode')) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized access to company data']);
        }

        $dosage = HerbisidaDosage::where([
            ['companycode', $companycode],
            ['herbisidagroupid', $herbisidagroupid],
            ['itemcode', $itemcode]
        ])->firstOrFail();
        
        $validated= $request->validate([
            'herbisidagroupid' => 'required|string|max:4',
            'itemcode'     => 'required|string|max:30',
            'description'  => 'nullable|string|max:100',
            'dosageperha'  => 'required|numeric',
        ]);

        // Check if the herbisidagroupid or itemcode has changed
        if (intval($request->herbisidagroupid) !== $dosage->herbisidagroupid ||
            $request->itemcode !== $dosage->itemcode) {
            
            $exists = HerbisidaDosage::where('companycode',  $companycode)
                ->where('herbisidagroupid', $request->herbisidagroupid)
                ->where('itemcode', $request->itemcode)
                ->exists();
            
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'herbisidagroupid' => 'Duplicate Entry, Data already exists'
                    ]);
            }
            
        } 
        
        HerbisidaDosage::where([
            ['companycode',   $companycode],
            ['herbisidagroupid',  $herbisidagroupid],
            ['itemcode',      $itemcode],
        ])->update([
            'companycode'   => $companycode, // Keep same company
            'herbisidagroupid'  => $validated['herbisidagroupid'],
            'itemcode'      => $validated['itemcode'],
            'dosageperha'   => $validated['dosageperha'],
            'updateby'      => Auth::user()->userid,
            'updatedat'     => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil diupdate.');
    }
    
    public function destroy(Request $request, $companycode, $herbisidagroupid, $itemcode)
    { 
        // Ensure user can only delete data for their session company
        if ($companycode !== session('companycode')) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized access to company data']);
        }

        HerbisidaDosage::where([
            ['companycode', $companycode],
            ['herbisidagroupid', $herbisidagroupid],
            ['itemcode', $itemcode]
        ])->delete();

        return redirect()->back()->with('success','Data berhasil dihapus.');
    }
}