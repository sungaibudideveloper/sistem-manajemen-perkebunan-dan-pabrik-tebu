<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Mandor;
use Illuminate\Support\Facades\DB;

class MandorController extends Controller
{
    /**
     * Display a listing of mandor.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $query = Mandor::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('companycode', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $mandor = $query
            ->orderBy('companycode')
            ->orderBy('id')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);

        return view('master.mandor.index', [
            'mandor'  => $mandor,
            'title'    => 'Data Mandor',
            'navbar'   => 'Master',
            'nav'      => 'Mandor',
            'perPage'  => $perPage,
            'search'   => $search,
        ]);
    }

    /**
     * Generate next mandor ID with M01, M02, M03 format
     */
    private function generateNextId($companycode)
    {
        // Get the latest ID for this company
        $latestMandor = Mandor::where('companycode', $companycode)
                              ->orderByRaw('CAST(SUBSTRING(id, 2) AS UNSIGNED) DESC')
                              ->first();
        
        if (!$latestMandor) {
            // No existing mandor for this company, start with M01
            return 'M01';
        }
        
        // Extract the numeric part and increment
        $idNumber = (int) substr($latestMandor->id, 1);
        $nextNumber = $idNumber + 1;
        
        // Format as M01, M02, etc.
        return 'M' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Store a newly created mandor in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'companycode' => 'required|string|size:4',
            'name'        => 'required|string|max:50',
        ]);

        // Generate the next ID in M01, M02, M03 format
        $nextId = $this->generateNextId($request->companycode);

        // Check if the combination already exists
        $exists = Mandor::where('companycode', $request->companycode)
                       ->where('id', $nextId)
                       ->exists();
                       
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'id' => 'Duplicate Entry, Mandor ID already exists'
                ]);
        }

        Mandor::create([
            'companycode' => $request->companycode,
            'id'          => $nextId,
            'name'        => $request->name,
            'inputby'     => Auth::user()->userid,
            'createdat'   => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil dibuat.');
    }

    /**
     * Update the specified mandor in storage.
     */
    public function update(Request $request, $companycode, $id)
    {
        $mandor = Mandor::where('companycode', $companycode)
                         ->where('id', $id)
                         ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $mandor->update([
            'name'      => $request->name,
            'updateby'  => Auth::user()->userid,
            'updatedat' => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil di-update.');
    }

    /**
     * Remove the specified mandor from storage.
     */
    public function destroy(Request $request, $companycode, $id)
    {
        Mandor::where('companycode', $companycode)
              ->where('id', $id)
              ->delete();

        return redirect()->back()->with('success', 'Data berhasil di-hapus.');
    }
}