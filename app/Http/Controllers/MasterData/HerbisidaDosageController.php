<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\HerbisidaDosage;

class HerbisidaDosageController extends Controller
{
    
    public function index(Request $request)
    {   
        $perPage = (int) $request->input('perPage', 10);
        $herbisidaDosages = HerbisidaDosage::paginate($perPage);
        return view('master.herbisidadosage.index', [
            'herbisidaDosages' => $herbisidaDosages,
            'title' => 'Data Dosis Herbisida',
            'navbar' => 'Master',
            'nav' => 'Dosis Herbisida',
            'perPage' => $perPage,
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
            ->exists();
        if ($exists) {
        return redirect()->back()
            ->withInput()
            ->withErrors([
                'activitycode' => 'Duplicate Entry, Activity Code already exists'
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
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'companycode'  => 'required|string|max:4',
            'activitycode' => 'required|string|max:50',
            'itemcode'     => 'required|string|max:30',
            'time'         => 'required|string|max:50',
            'description'  => 'nullable|string|max:100',
            'totaldosage'  => 'required|numeric',
            'dosageunit'   => 'required|string|max:5',
        ]);
        
        $dosage = HerbisidaDosage::findOrFail($id);
        if (
            $request->companycode  !== $dosage->companycode ||
            $request->activitycode !== $dosage->activitycode
        ) {
            $exists = HerbisidaDosage::where('companycode',  $request->companycode)
                ->where('activitycode', $request->activitycode)
                ->exists();
    
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'activitycode' => 'Duplicate Entry, Activity Code already exists'
                    ]);
            }
        }
        
        $dosage = HerbisidaDosage::findOrFail($id);
        $dosage->update($request->only([
            'companycode',
            'activitycode',
            'itemcode',
            'time',
            'description',
            'totaldosage',
            'dosageunit'
        ]));
    
        return redirect()->back()->with('success', 'Data berhasil di‑update.');
    }

    public function destroy(Request $request, string $activitycode)
{
    $company  = $request->input('companycode');
    $itemcode = $request->input('itemcode');

    HerbisidaDosage::where('companycode', $company)
        ->where('activitycode', $activitycode)
        ->where('itemcode', $itemcode)
        ->delete();

    return redirect()->back()->with('success','Data berhasil di‑hapus.');
}
}
