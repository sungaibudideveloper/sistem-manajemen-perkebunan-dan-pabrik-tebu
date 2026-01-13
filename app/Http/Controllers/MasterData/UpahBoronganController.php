<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\User;

class UpahBoronganController extends Controller
{
    public function index(Request $request)
    {
        // Query dengan join ke tabel activity untuk ambil activitygroup dan activityname
        $query = DB::table('upahborongan as ub')
            ->join('activity as a', 'ub.activitycode', '=', 'a.activitycode')
            ->select(
                'ub.id',
                'ub.companycode',
                'ub.activitycode',
                'a.activitygroup',
                'a.activityname',
                'ub.amount',
                'ub.effectivedate',
                'ub.enddate',
                'ub.inputby',
                'ub.createdat',
                'ub.updatedat'
            );

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ub.activitycode', 'like', "%{$search}%")
                    ->orWhere('a.activityname', 'like', "%{$search}%")
                    ->orWhere('a.activitygroup', 'like', "%{$search}%")
                    ->orWhere('ub.amount', 'like', "%{$search}%");
            });
        }

        // Filter by activitygroup
        if ($request->filled('activitygroup')) {
            $query->where('a.activitygroup', $request->activitygroup);
        }

        // Filter by status (active/expired)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where(function ($q) {
                    $q->whereNull('ub.enddate')
                        ->orWhere('ub.enddate', '>=', now()->format('Y-m-d'));
                });
            } elseif ($request->status === 'expired') {
                $query->where('ub.enddate', '<', now()->format('Y-m-d'));
            }
        }

        // Filter berdasarkan company user
        $query->where('ub.companycode', Session::get('companycode'));

        $perPage = $request->get('perPage', 10);
        $data = $query->orderBy('ub.effectivedate', 'DESC')
            ->orderBy('a.activitygroup', 'ASC')
            ->paginate($perPage);

        $user = Auth::user()->userid;
        $userdata = User::where('userid', $user)->firstOrFail();

        // Daftar activity groups untuk filter dropdown
        $activityGroups = DB::table('activity')
            ->select('activitygroup')
            ->whereNotNull('activitygroup')
            ->distinct()
            ->orderBy('activitygroup')
            ->pluck('activitygroup');

        return view('masterdata.upah-borongan.index', [
            'title' => 'Upah Borongan',
            'navbar' => 'Master Data',
            'nav' => 'Upah Borongan',
            'data' => $data,
            'perPage' => $perPage,
            'userdata' => $userdata,
            'activityGroups' => $activityGroups,
        ]);
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'activitycode' => 'required|exists:activity,activitycode',
            'amount' => 'required|numeric|min:0',
            'effectivedate' => 'required|date',
            'enddate' => 'nullable|date|after_or_equal:effectivedate',
        ], [
            'activitycode.required' => 'Aktivitas harus dipilih',
            'activitycode.exists' => 'Aktivitas tidak valid',
            'amount.required' => 'Nominal upah harus diisi',
            'amount.numeric' => 'Nominal upah harus berupa angka',
            'amount.min' => 'Nominal upah tidak boleh negatif',
            'effectivedate.required' => 'Tanggal mulai berlaku harus diisi',
            'effectivedate.date' => 'Format tanggal tidak valid',
            'enddate.date' => 'Format tanggal berakhir tidak valid',
            'enddate.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai berlaku',
        ]);

        $companycode = Session::get('companycode');

        // ✅ VALIDASI: Cek apakah sudah ada upah aktif untuk activity ini
        $existingActive = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $request->activitycode)
            ->where(function ($q) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', now()->format('Y-m-d'));
            })
            ->first();

        if ($existingActive) {
            $activityName = DB::table('activity')
                ->where('activitycode', $request->activitycode)
                ->value('activityname');

            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', "Tidak dapat menambah upah baru! Masih ada upah aktif untuk aktivitas [{$request->activitycode} - {$activityName}] dengan nominal Rp " . number_format($existingActive->amount, 0, ',', '.') . " yang berlaku sejak " . date('d-m-Y', strtotime($existingActive->effectivedate)) . ". Silakan EDIT upah tersebut dan set tanggal berakhir terlebih dahulu.");
        }

        // Cek duplikasi untuk periode yang sama
        $duplicate = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $request->activitycode)
            ->where('effectivedate', $request->effectivedate)
            ->exists();

        if ($duplicate) {
            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', 'Upah untuk aktivitas ini pada tanggal yang sama sudah ada.');
        }

        // Insert data
        DB::table('upahborongan')->insert([
            'companycode' => $companycode,
            'activitycode' => $request->activitycode,
            'amount' => $request->amount,
            'effectivedate' => $request->effectivedate,
            'enddate' => $request->enddate,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
            'updatedat' => now(),
        ]);

        return redirect()->route('masterdata.upah-borongan.index')
            ->with('success', 'Data upah borongan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'activitycode' => 'required|exists:activity,activitycode',
            'amount' => 'required|numeric|min:0',
            'effectivedate' => 'required|date',
            'enddate' => 'nullable|date|after_or_equal:effectivedate',
        ], [
            'activitycode.required' => 'Aktivitas harus dipilih',
            'activitycode.exists' => 'Aktivitas tidak valid',
            'amount.required' => 'Nominal upah harus diisi',
            'amount.numeric' => 'Nominal upah harus berupa angka',
            'amount.min' => 'Nominal upah tidak boleh negatif',
            'effectivedate.required' => 'Tanggal mulai berlaku harus diisi',
            'effectivedate.date' => 'Format tanggal tidak valid',
            'enddate.date' => 'Format tanggal berakhir tidak valid',
            'enddate.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai berlaku',
        ]);

        $companycode = Session::get('companycode');

        // Cek apakah data exist
        $existing = DB::table('upahborongan')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->first();

        if (!$existing) {
            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', 'Data upah borongan tidak ditemukan.');
        }

        // Cek duplikasi (exclude current record)
        $duplicate = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $request->activitycode)
            ->where('effectivedate', $request->effectivedate)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', 'Upah untuk aktivitas ini pada tanggal yang sama sudah ada.');
        }

        // ✅ VALIDASI: Cek overlap dengan upah aktif lain (exclude current record)
        $hasOtherActive = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $request->activitycode)
            ->where('id', '!=', $id)
            ->where(function ($q) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', now()->format('Y-m-d'));
            })
            ->first();

        if ($hasOtherActive && empty($request->enddate)) {
            $activityName = DB::table('activity')
                ->where('activitycode', $request->activitycode)
                ->value('activityname');

            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', "Tidak dapat mengaktifkan upah ini! Masih ada upah aktif lain untuk aktivitas [{$request->activitycode} - {$activityName}] dengan nominal Rp " . number_format($hasOtherActive->amount, 0, ',', '.') . " yang berlaku sejak " . date('d-m-Y', strtotime($hasOtherActive->effectivedate)) . ". Silakan set tanggal berakhir pada upah tersebut terlebih dahulu.");
        }

        // Update data
        DB::table('upahborongan')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->update([
                'activitycode' => $request->activitycode,
                'amount' => $request->amount,
                'effectivedate' => $request->effectivedate,
                'enddate' => $request->enddate,
                'updatedat' => now(),
            ]);

        return redirect()->route('masterdata.upah-borongan.index')
            ->with('success', 'Data upah borongan berhasil diupdate');
    }

    public function destroy($id)
    {
        $companycode = Session::get('companycode');

        // Cek apakah data exist
        $existing = DB::table('upahborongan')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->first();

        if (!$existing) {
            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', 'Data upah borongan tidak ditemukan.');
        }

        // Optional: Cek apakah upah ini sudah dipakai di transaksi
        // Uncomment jika ada tabel transaksi yang reference ke upahborongan
        /*
        $isUsed = DB::table('transaksi_table')
            ->where('upahborongan_id', $id)
            ->exists();

        if ($isUsed) {
            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', 'Data upah borongan tidak dapat dihapus karena sudah digunakan dalam transaksi.');
        }
        */

        // Delete data
        DB::table('upahborongan')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->delete();

        return redirect()->route('masterdata.upah-borongan.index')
            ->with('success', 'Data upah borongan berhasil dihapus.');
    }

    /**
     * Get activities by group (untuk AJAX)
     */
    public function getActivitiesByGroup(Request $request)
    {
        $activitygroup = $request->get('activitygroup');

        $activities = DB::table('activity')
            ->select('activitycode', 'activityname')
            ->where('activitygroup', $activitygroup)
            ->where('active', 1)
            ->orderBy('activityname')
            ->get();

        return response()->json($activities);
    }

    /**
     * Get current wage for activity (untuk reference di form)
     */
    public function getCurrentWage(Request $request)
    {
        $activitycode = $request->get('activitycode');
        $companycode = Session::get('companycode');

        $currentWage = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $activitycode)
            ->where(function ($q) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', now()->format('Y-m-d'));
            })
            ->orderBy('effectivedate', 'DESC')
            ->first();

        return response()->json($currentWage);
    }
}