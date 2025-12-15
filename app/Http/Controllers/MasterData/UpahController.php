<?php

namespace App\Http\Controllers\Masterdata;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\Submenu;
use App\Models\MasterData\Upah;
use App\Models\User;

class UpahController extends Controller
{
    public function index(Request $request)
    {
        // Query dengan struktur tabel yang benar
        $query = DB::table('upah')
            ->select('*');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('activitygroup', 'like', '%' . $request->search . '%')
                    ->orWhere('wagetype', 'like', '%' . $request->search . '%')
                    ->orWhere('effectivedate', 'like', '%' . $request->search . '%');
            });
        }

        // Filter berdasarkan company user
        $query->where('companycode', Auth::user()->companycode);

        $perPage = $request->get('perPage', 10);
        $data = $query->orderBy('effectivedate', 'DESC')->paginate($perPage);

        $user = Auth::user()->userid;
        $userdata = User::where('userid', $user)->firstOrFail();

        // Daftar activity group dan wage type untuk dropdown
        $activityGroups = DB::table('upah')
            ->select('activitygroup')
            ->where('companycode', Auth::user()->companycode)
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
            'title' => 'Menu',
            'navbar' => 'Aplikasi', 
            'nav' => 'Menu',
            'data' => $data,
            'perPage' => $perPage,
            'userdata' => $userdata,
            'activityGroups' => $activityGroups,
            'wageTypes' => $wageTypes,
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->userid && in_array('Create Upah', json_decode(Auth::user()->permissions ?? '[]'))) {

            // Validasi duplikasi data
            $cek = DB::table('upah')
                ->where('companycode', Auth::user()->companycode)
                ->where('activitygroup', $request->activitygroup)
                ->where('wagetype', $request->wagetype)
                ->where('effectivedate', $request->effectivedate)
                ->where('parameter', $request->parameter)
                ->exists();

            if ($cek) {
                return redirect()->route('masterdata.upah.index')
                    ->with('error', 'Data upah dengan kombinasi yang sama sudah ada.');
            }

            $upah = new Upah();
            $upah->companycode = Auth::user()->companycode;
            $upah->activitygroup = $request->activitygroup;
            $upah->wagetype = $request->wagetype;
            $upah->amount = $request->amount;
            $upah->effectivedate = $request->effectivedate;
            $upah->enddate = $request->enddate;
            $upah->parameter = $request->parameter;
            $upah->inputby = Auth::user()->userid;
            $upah->createdat = now();
            $upah->save();

            return redirect()->route('masterdata.upah.index')
                ->with('success', 'Data upah berhasil ditambahkan');
        } else {
            return redirect()->route('masterdata.upah.index')
                ->with('error', 'Anda tidak memiliki izin untuk menambah data upah.');
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->userid && in_array('Edit Upah', json_decode(Auth::user()->permissions ?? '[]'))) {
            $company = Auth::user()->companycode;

            // Validasi duplikasi data (kecuali data yang sedang diedit)
            $validasi = DB::table('upah')
                ->where('companycode', $company)
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
                ->where('companycode', $company)
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
            } else {
                return redirect()->route('masterdata.upah.index')
                    ->with('error', 'Data upah tidak ditemukan.');
            }
        } else {
            return redirect()->route('masterdata.upah.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengedit data upah.');
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->userid && in_array('Hapus Upah', json_decode(Auth::user()->permissions ?? '[]'))) {
            $company = Auth::user()->companycode;

            $deleted = DB::table('upah')
                ->where('id', $id)
                ->where('companycode', $company)
                ->delete();

            if ($deleted) {
                return redirect()->route('masterdata.upah.index')
                    ->with('success', 'Data berhasil dihapus.');
            } else {
                return redirect()->route('masterdata.upah.index')
                    ->with('error', 'Data upah tidak ditemukan.');
            }
        } else {
            return redirect()->route('masterdata.upah.index')
                ->with('error', 'Anda tidak memiliki izin untuk menghapus data upah.');
        }
    }
}