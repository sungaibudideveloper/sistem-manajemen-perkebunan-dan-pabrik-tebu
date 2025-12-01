<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TenagaKerja;
use Illuminate\Support\Facades\DB;

class TenagaKerjaController extends Controller
{
    /**
     * Display a listing of tenaga kerja.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search = $request->input('search');
        $companycode = session('companycode');

        // Query dengan explicit SELECT dan proper JOIN
        $query = DB::table('tenagakerja as tk')
            ->leftJoin('jenistenagakerja as jtk', 'tk.jenistenagakerja', '=', 'jtk.idjenistenagakerja')
            ->leftJoin('user as u', 'tk.mandoruserid', '=', 'u.userid')
            ->where('tk.companycode', $companycode)
            ->select([
                'tk.tenagakerjaid',
                'tk.mandoruserid',
                'tk.companycode',
                'tk.nama',
                'tk.nik',
                'tk.gender',
                'tk.jenistenagakerja',
                'tk.isactive',
                'jtk.nama as jenis_nama',
                'u.name as mandor_nama'
            ]);

        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('tk.nama', 'like', "%{$search}%")
                  ->orWhere('tk.nik', 'like', "%{$search}%")
                  ->orWhere('tk.tenagakerjaid', 'like', "%{$search}%")
                  ->orWhere('u.name', 'like', "%{$search}%")
                  ->orWhere('jtk.nama', 'like', "%{$search}%");
            });
        }

        $result = $query
            ->orderBy('tk.isactive', 'desc')
            ->orderBy('tk.tenagakerjaid', 'asc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search' => $search,
            ]);

        // Get mandor list (idjabatan = 5, active only, filtered by company)
        $mandor = User::where('idjabatan', 5)
            ->where('isactive', 1)
            ->where('companycode', $companycode)
            ->orderBy('name')
            ->get();

        // Get jenis tenaga kerja list (include all types)
        $jenistenagakerja = DB::table('jenistenagakerja')
            ->orderBy('idjenistenagakerja')
            ->get();

        return view('master.tenagakerja.index', [
            'result' => $result,
            'title' => 'Data Tenaga Kerja',
            'navbar' => 'Master',
            'nav' => 'Tenaga Kerja',
            'perPage' => $perPage,
            'search' => $search,
            'mandor' => $mandor,
            'jenistenagakerja' => $jenistenagakerja,
            'companycode' => $companycode
        ]);
    }

    /**
     * Generate next tenaga kerja ID with M0001, M0002 format
     */
    private function generateNextId($companycode)
    {
        // Get the latest ID for this company
        $latestRecord = TenagaKerja::where('companycode', $companycode)
            ->orderByRaw('CAST(SUBSTRING(tenagakerjaid, 2) AS UNSIGNED) DESC')
            ->first();

        if (!$latestRecord) {
            // No existing record for this company, start with M0001
            return 'M0001';
        }

        // Extract the numeric part and increment
        $idNumber = (int) substr($latestRecord->tenagakerjaid, 1);
        $nextNumber = $idNumber + 1;

        // Format as M0001, M0002, etc.
        return 'M' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Store a newly created tenaga kerja in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'nik' => 'required|string|max:16|unique:tenagakerja,nik',
            'mandor' => 'required|exists:user,userid',
            'gender' => 'required|in:L,P',
            'jenis' => 'required|exists:jenistenagakerja,idjenistenagakerja',
        ]);


        $companycode = session('companycode');
        
        // Generate the next ID
        $nextId = $this->generateNextId($companycode);

        // Double check if the ID already exists
        $exists = TenagaKerja::where('companycode', $companycode)
            ->where('tenagakerjaid', $nextId)
            ->exists();
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['id' => 'Gagal mendapatkan ID unik']);
        }
        TenagaKerja::create([
            'tenagakerjaid' => $nextId,
            'mandoruserid' => $request->mandor,
            'companycode' => $companycode,
            'nama' => $request->name,
            'nik' => $request->nik,
            'gender' => $request->gender,
            'jenistenagakerja' => $request->jenis,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
            'isactive' => 1
        ]);

        return redirect()->back()->with('success', 'Data tenaga kerja berhasil ditambahkan.');
    }

    /**
     * Update the specified tenaga kerja in storage.
     */
    public function update(Request $request, $companycode, $id)
    {
        $tenagaKerja = TenagaKerja::where('companycode', $companycode)
            ->where('tenagakerjaid', $id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:100',
            'nik' => 'required|string|max:16|unique:tenagakerja,nik,' . $id . ',tenagakerjaid',
            'mandor' => 'required|exists:user,userid',
            'gender' => 'required|in:L,P',
            'jenis' => 'required|exists:jenistenagakerja,idjenistenagakerja',
            'isactive' => 'nullable|boolean'
        ]);

        $tenagaKerja->update([
            'mandoruserid' => $request->mandor,
            'nama' => $request->name,
            'nik' => $request->nik,
            'gender' => $request->gender,
            'jenistenagakerja' => $request->jenis,
            'updateby' => Auth::user()->userid,
            'updatedat' => now(),
            'isactive' => $request->has('isactive') ? 1 : 0
        ]);

        return redirect()->back()->with('success', 'Data tenaga kerja berhasil diperbarui.');
    }

    /**
     * Soft delete (set inactive) the specified tenaga kerja.
     */
    public function destroy($companycode, $id)
    {
        $tenagaKerja = TenagaKerja::where('companycode', $companycode)
            ->where('tenagakerjaid', $id)
            ->firstOrFail();

        $tenagaKerja->update([
            'isactive' => 0,
            'updateby' => Auth::user()->userid,
            'updatedat' => now()
        ]);

        return redirect()->back()->with('success', 'Data tenaga kerja berhasil dinonaktifkan.');
    }
}