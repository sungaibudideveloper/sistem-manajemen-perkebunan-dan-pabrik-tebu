<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Accounting;
use Illuminate\Support\Facades\Auth;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)$request->input('perPage', 10);
        $search  = $request->input('search');

        $query = Accounting::query();
        if ($search) {
            $query->where(fn($q) =>
                $q->where('activitycode', 'like', "%{$search}%")
                  ->orWhere('jurnalaccno', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
            );
        }

        $accounting = $query
            ->orderBy('activitycode')
            ->paginate($perPage)
            ->appends(compact('perPage', 'search'));

        return view('master.accounting.index', [
            'accounting' => $accounting,
            'title'      => 'Data Accounting',
            'navbar'     => 'Master',
            'nav'        => 'Accounting',
            'perPage'    => $perPage,
            'search'     => $search,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'activitycode'  => 'required|string|max:50',
            'jurnalaccno'   => 'required|string|size:12',
            'jurnalacctype' => 'required|string|max:1',
            'description'   => 'nullable|string|max:100',
        ]);

        // Cek duplicate dengan 3-field composite key
        if (Accounting::where('activitycode', $request->activitycode)
                       ->where('jurnalaccno',   $request->jurnalaccno)
                       ->where('jurnalacctype', $request->jurnalacctype)
                       ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors(['activitycode' => 'Duplicate Entry, kombinasi kode sudah ada']);
        }

        Accounting::create([
            'activitycode'  => $request->activitycode,
            'jurnalaccno'   => $request->jurnalaccno,
            'jurnalacctype' => $request->jurnalacctype,
            'description'   => $request->description,
            'inputby'       => Auth::user()->userid,
            'createdat'     => now(),
        ]);

        return back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $activitycode, $jurnalaccno, $jurnalacctype)
    {
        $accounting = Accounting::where('activitycode',  $activitycode)
                                ->where('jurnalaccno',    $jurnalaccno)
                                ->where('jurnalacctype',  $jurnalacctype)
                                ->firstOrFail();

        $validated = $request->validate([
            'activitycode'  => 'required|string|max:50',
            'jurnalaccno'   => 'required|string|size:12',
            'jurnalacctype' => 'required|string|max:1',
            'description'   => 'nullable|string|max:100',
        ]);

        if (
            (
                $validated['activitycode']  !== $accounting->activitycode ||
                $validated['jurnalaccno']   !== $accounting->jurnalaccno   ||
                $validated['jurnalacctype'] !== $accounting->jurnalacctype
            )
            && Accounting::where('activitycode',  $validated['activitycode'])
                         ->where('jurnalaccno',    $validated['jurnalaccno'])
                         ->where('jurnalacctype',  $validated['jurnalacctype'])
                         ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors(['activitycode' => 'Duplicate Entry, kombinasi kode sudah ada']);
        }

        // Update langsung lewat model karena sudah ada composite key, kalau tidak boleh pake $accounting->update()
        Accounting::where([
            ['activitycode',  $activitycode],
            ['jurnalaccno',   $jurnalaccno],
            ['jurnalacctype', $jurnalacctype],
        ])->update([
            'activitycode'  => $validated['activitycode'],  
            'jurnalaccno'   => $validated['jurnalaccno'],   
            'jurnalacctype' => $validated['jurnalacctype'], 
            'description'   => $validated['description'],   
            'updateby'      => Auth::user()->userid,
            'updatedat'     => now(),
        ]);

        return back()->with('success', 'Data berhasil di-update.');
    }

    public function destroy($activitycode, $jurnalaccno, $jurnalacctype)
    {
        // Hapus berdasarkan composite key 3 kolom
        $accounting = Accounting::where('activitycode',  $activitycode)
                                ->where('jurnalaccno',    $jurnalaccno)
                                ->where('jurnalacctype',  $jurnalacctype)
                                ->firstOrFail();
        $accounting->delete();

        return back()->with('success', 'Data berhasil di-hapus.');
    }
}
