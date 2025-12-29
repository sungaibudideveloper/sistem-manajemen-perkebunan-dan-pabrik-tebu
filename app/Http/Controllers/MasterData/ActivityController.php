<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Activity;
use App\Models\MasterData\ActivityGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 50);
        $search = $request->input('search');

        $query = Activity::with('group');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('activitycode', 'like', "%{$search}%")
                  ->orWhere('activityname', 'like', "%{$search}%")
                  ->orWhere('activitygroup', 'like', "%{$search}%");
            });
        }

        $activities = $query
            ->orderBy('activitygroup', 'asc')
            ->orderBy('activitycode', 'asc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search' => $search,
            ]);

        $activityGroup = ActivityGroup::orderBy('activitygroup', 'asc')->get();

        return view('masterdata.activity.index', [
            'title' => 'Daftar Aktivitas',
            'navbar' => 'Master',
            'nav' => 'aktivitas',
            'perPage' => $perPage,
            'search' => $search,
            'activities' => $activities,
            'activityGroup' => $activityGroup
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'grupaktivitas' => 'required|exists:activitygroup,activitygroup',
            'kodeaktivitas' => 'required|string|max:3',
            'namaaktivitas' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:150',
            'jenistenagakerja' => 'required|in:1,2',
            'material' => 'required|in:0,1',
            'vehicle' => 'required|in:0,1',
            'var.*' => 'required|string|max:50',
            'satuan.*' => 'required|string|max:20'
        ], [
            'kodeaktivitas.max' => 'Kode aktivitas maksimal 3 karakter',
            'var.*.required' => 'Variable hasil aktivitas wajib diisi',
            'satuan.*.required' => 'Satuan hasil aktivitas wajib diisi'
        ]);

        // Cek duplicate
        $exists = Activity::where('activitycode', $request->kodeaktivitas)->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['kodeaktivitas' => 'Kode aktivitas sudah ada dalam database']);
        }

        // Validasi max 5 variables
        if (count($request->var) > 5) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['var' => 'Maksimal 5 variable hasil aktivitas']);
        }

        try {
            DB::transaction(function () use ($request) {
                $data = [
                    'activitycode' => $request->kodeaktivitas,
                    'activitygroup' => $request->grupaktivitas,
                    'activityname' => $request->namaaktivitas,
                    'description' => $request->keterangan,
                    'usingmaterial' => $request->material,
                    'usingvehicle' => $request->vehicle,
                    'jenistenagakerja' => $request->jenistenagakerja,
                    'jumlahvar' => count($request->var),
                    'inputby' => Auth::user()->userid,
                    'createdat' => now(),
                ];

                // Tambahkan var dan satuan
                foreach ($request->var as $index => $value) {
                    $data["var" . ($index + 1)] = $value;
                    $data["satuan" . ($index + 1)] = $request->satuan[$index];
                }

                Activity::create($data);
            });

            return redirect()->back()->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error pada database: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $activityCode)
    {
        $request->validate([
            'grupaktivitas' => 'required|exists:activitygroup,activitygroup',
            'kodeaktivitas' => 'required|string|max:3',
            'namaaktivitas' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:150',
            'jenistenagakerja' => 'required|in:1,2',
            'material' => 'required|in:0,1',
            'vehicle' => 'required|in:0,1',
            'var.*' => 'required|string|max:50',
            'satuan.*' => 'required|string|max:20'
        ], [
            'kodeaktivitas.max' => 'Kode aktivitas maksimal 3 karakter',
            'var.*.required' => 'Variable hasil aktivitas wajib diisi',
            'satuan.*.required' => 'Satuan hasil aktivitas wajib diisi'
        ]);

        $activity = Activity::where('activitycode', $activityCode)->firstOrFail();

        // Validasi max 5 variables
        if (count($request->var) > 5) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['var' => 'Maksimal 5 variable hasil aktivitas']);
        }

        try {
            DB::transaction(function () use ($request, $activity) {
                $data = [
                    'activitycode' => $request->kodeaktivitas,
                    'activitygroup' => $request->grupaktivitas,
                    'activityname' => $request->namaaktivitas,
                    'description' => $request->keterangan,
                    'usingmaterial' => $request->material,
                    'usingvehicle' => $request->vehicle,
                    'jenistenagakerja' => $request->jenistenagakerja,
                    'jumlahvar' => count($request->var),
                    'updateby' => Auth::user()->userid,
                    'updatedat' => now(),
                ];

                // Reset semua var dan satuan
                for ($i = 1; $i <= 5; $i++) {
                    $data["var$i"] = null;
                    $data["satuan$i"] = null;
                }

                // Tambahkan var dan satuan yang ada
                foreach ($request->var as $index => $value) {
                    $data["var" . ($index + 1)] = $value;
                    $data["satuan" . ($index + 1)] = $request->satuan[$index];
                }

                $activity->update($data);
            });

            return redirect()->back()->with('success', 'Data berhasil di-update');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error pada database: ' . $e->getMessage()]);
        }
    }

    public function destroy($activityCode)
    {
        try {
            DB::transaction(function () use ($activityCode) {
                Activity::where('activitycode', $activityCode)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }
}