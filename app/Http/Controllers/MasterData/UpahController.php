<?php

namespace App\Http\Controllers\Masterdata;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\User;

class UpahController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('upah')->select('*');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('activitygroup', 'like', '%' . $request->search . '%')
                    ->orWhere('wagetype', 'like', '%' . $request->search . '%')
                    ->orWhere('effectivedate', 'like', '%' . $request->search . '%');
            });
        }

        $query->where('companycode', Session::get('companycode'));

        $perPage = $request->get('perPage', 10);
        $data = $query->orderBy('effectivedate', 'DESC')->paginate($perPage);

        $user = Auth::user()->userid;
        $userdata = User::where('userid', $user)->firstOrFail();

        $activityGroups = DB::table('upah')
            ->select('activitygroup')
            ->where('companycode', Session::get('companycode'))
            ->distinct()
            ->pluck('activitygroup');

        $wageTypes = [
            'DAILY' => 'Harian',
            'HOURLY' => 'Per Jam', 
            'OVERTIME' => 'Lembur',
            'WEEKEND_SATURDAY' => 'Weekend Sabtu',
            'WEEKEND_SUNDAY' => 'Weekend Minggu',
            'PER_HECTARE' => 'Per Hektar',
            'PER_KG' => 'Per Kilogram'
        ];

        return view('masterdata.upah.index', [
            'title' => 'Upah',
            'navbar' => 'Master Data', 
            'nav' => 'Upah',
            'data' => $data,
            'perPage' => $perPage,
            'userdata' => $userdata,
            'activityGroups' => $activityGroups,
            'wageTypes' => $wageTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'activitygroup' => 'required',
            'wagetype' => 'required',
            'amount' => 'required|numeric|min:0',
            'effectivedate' => 'required|date',
            'enddate' => 'nullable|date|after_or_equal:effectivedate',
        ], [
            'activitygroup.required' => 'Grup aktivitas harus diisi',
            'wagetype.required' => 'Jenis upah harus dipilih',
            'amount.required' => 'Nominal upah harus diisi',
            'amount.numeric' => 'Nominal upah harus berupa angka',
            'amount.min' => 'Nominal upah tidak boleh negatif',
            'effectivedate.required' => 'Tanggal mulai berlaku harus diisi',
            'enddate.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai berlaku',
        ]);

        $companycode = Session::get('companycode');

        // Validasi duplikasi data
        $cek = DB::table('upah')
            ->where('companycode', $companycode)
            ->where('activitygroup', $request->activitygroup)
            ->where('wagetype', $request->wagetype)
            ->where('effectivedate', $request->effectivedate)
            ->where('parameter', $request->parameter)
            ->exists();

        if ($cek) {
            return redirect()->route('masterdata.upah.index')
                ->with('error', 'Data upah dengan kombinasi yang sama sudah ada.');
        }

        DB::table('upah')->insert([
            'companycode' => $companycode,
            'activitygroup' => $request->activitygroup,
            'wagetype' => $request->wagetype,
            'amount' => $request->amount,
            'effectivedate' => $request->effectivedate,
            'enddate' => $request->enddate,
            'parameter' => $request->parameter,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
        ]);

        return redirect()->route('masterdata.upah.index')
            ->with('success', 'Data upah berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'activitygroup' => 'required',
            'wagetype' => 'required',
            'amount' => 'required|numeric|min:0',
            'effectivedate' => 'required|date',
            'enddate' => 'nullable|date|after_or_equal:effectivedate',
        ], [
            'activitygroup.required' => 'Grup aktivitas harus diisi',
            'wagetype.required' => 'Jenis upah harus dipilih',
            'amount.required' => 'Nominal upah harus diisi',
            'amount.numeric' => 'Nominal upah harus berupa angka',
            'amount.min' => 'Nominal upah tidak boleh negatif',
            'effectivedate.required' => 'Tanggal mulai berlaku harus diisi',
            'enddate.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai berlaku',
        ]);

        $companycode = Session::get('companycode');

        // Validasi duplikasi data (kecuali data yang sedang diedit)
        $validasi = DB::table('upah')
            ->where('companycode', $companycode)
            ->where('activitygroup', $request->activitygroup)
            ->where('wagetype', $request->wagetype)
            ->where('effectivedate', $request->effectivedate)
            ->where('parameter', $request->parameter)
            ->where('id', '!=', $id)
            ->exists();

        if ($validasi) {
            return redirect()->route('masterdata.upah.index')
                ->with('error', 'Data upah dengan kombinasi yang sama sudah ada.');
        }

        $updated = DB::table('upah')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->update([
                'activitygroup' => $request->activitygroup,
                'wagetype' => $request->wagetype,
                'amount' => $request->amount,
                'effectivedate' => $request->effectivedate,
                'enddate' => $request->enddate,
                'parameter' => $request->parameter,
                'updatedat' => now(),
            ]);

        if ($updated) {
            return redirect()->route('masterdata.upah.index')
                ->with('success', 'Data upah berhasil diupdate');
        }

        return redirect()->route('masterdata.upah.index')
            ->with('error', 'Data upah tidak ditemukan.');
    }

    public function destroy($id)
    {
        $companycode = Session::get('companycode');

        $deleted = DB::table('upah')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->delete();

        if ($deleted) {
            return redirect()->route('masterdata.upah.index')
                ->with('success', 'Data berhasil dihapus.');
        }

        return redirect()->route('masterdata.upah.index')
            ->with('error', 'Data upah tidak ditemukan.');
    }
}