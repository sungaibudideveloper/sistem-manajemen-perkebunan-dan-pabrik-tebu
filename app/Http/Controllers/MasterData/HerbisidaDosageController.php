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
            'activitycode' => 'required|string|max:50',
            'itemcode' => 'required|string|max:30',
            'time' => 'required|string|max:50',
            'description' => 'required|string|max:100',
            'totaldosage' => 'required|numeric',
            'dosageunit' => 'required|string|max:5',
        ]);

        HerbisidaDosage::create([
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
    }
}
