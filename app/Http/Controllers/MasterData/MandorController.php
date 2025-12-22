<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
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
        $companycode = session('companycode');

        $query = User::where('companycode', $companycode)
                    ->where('idjabatan', 5);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('userid', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $mandor = $query
            ->orderBy('isactive', 'desc')
            ->orderBy('userid')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);

        return view('masterdata.mandor.index', [
            'mandor'       => $mandor,
            'title'        => 'Data Mandor',
            'navbar'       => 'Master',
            'nav'          => 'Mandor',
            'perPage'      => $perPage,
            'search'       => $search,
            'companycode'  => $companycode,
        ]);
    }

    /**
     * Generate next mandor ID with M001, M002, M003 format
     */
    private function generateNextId($companycode)
    {
        // Get the latest ID for this company
        $latestMandor = User::where('companycode', $companycode)
                            ->where('idjabatan', 5)
                            ->orderByRaw('CAST(SUBSTRING(userid, 2) AS UNSIGNED) DESC')
                            ->first();

        if (!$latestMandor) {
            // No existing mandor for this company, start with M001
            return 'M001';
        }

        // Extract the numeric part and increment
        $idNumber = (int) substr($latestMandor->userid, 1);
        $nextNumber = $idNumber + 1;

        // Format as M001, M002, etc.
        return 'M' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Store a newly created mandor in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:30',
        ]);

        $companycode = session('companycode');
        
        // Generate the next ID in M001, M002, M003 format
        $nextId = $this->generateNextId($companycode);

        // Check if the combination already exists
        $exists = User::where('companycode', $companycode)
                       ->where('userid', $nextId)
                       ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'userid' => 'Duplicate Entry, Mandor ID already exists'
                ]);
        }

        User::create([
            'companycode' => $companycode,
            'userid'      => $nextId,
            'idjabatan'   => 5,
            'password'    => bcrypt('sungaibudi'),
            'name'        => $request->name,
            'inputby'     => Auth::user()->userid,
            'createdat'   => now(),
            'isactive'    => 1
        ]);

        return redirect()->back()->with('success', "Mandor berhasil dibuat! ID: {$nextId}, Company: {$companycode}, Password: 'sungaibudi'");
    }

    /**
     * Update the specified mandor in storage.
     */
    public function update(Request $request, $companycode, $userid)
    {
        $mandor = User::where('companycode', $companycode)
                     ->where('userid', $userid)
                     ->where('idjabatan', 5)
                     ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:30',
            'isactive' => 'nullable|boolean',
        ]);

        $mandor->update([
            'name'       => $request->name,
            'isactive'   => $request->has('isactive') ? 1 : 0,
            'updateby'   => Auth::user()->userid,
            'updatedat'  => now()
        ]);

        return redirect()->back()->with('success', 'Data mandor berhasil di-update.');
    }

    /**
     * Remove the specified mandor from storage (soft delete).
     */
    public function destroy($companycode, $userid)
    {
        $mandor = User::where('companycode', $companycode)
                     ->where('userid', $userid)
                     ->where('idjabatan', 5)
                     ->firstOrFail();

        $mandor->update([
            'isactive'   => 0,
            'updateby'   => Auth::user()->userid,
            'updatedat'  => now()
        ]);

        return redirect()->back()->with('success', 'Data mandor berhasil di non-aktifkan.');
    }
}