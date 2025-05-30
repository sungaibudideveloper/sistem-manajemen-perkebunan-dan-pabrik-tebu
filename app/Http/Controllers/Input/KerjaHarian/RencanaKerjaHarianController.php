<?php

namespace App\Http\Controllers\Input\KerjaHarian;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\RkhHdr;
use App\Models\Mandor;
use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\Blok;
use App\Models\Masterlist;
use App\Models\Herbisidadosage;
use App\Models\Herbisidagroup;
use App\Models\AbsenTenagaKerja;



class RencanaKerjaHarianController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        return view('input.kerjaharian.rencanakerjaharian.index', [
            'title'     => 'Rencana Kerja Harian',
            'navbar'    => 'Input',
            'nav'       => 'Rencana Kerja Harian',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }

     public function store(Request $request)
    {
$filteredRows = collect($request->input('rows', []))
    ->filter(function ($row) {
        // Lebih fleksibel - hanya perlu salah satu field yang tidak kosong
        return !empty($row['blok']) || !empty($row['plot']) || !empty($row['nama']);
    })
    ->map(function ($row) {
        // Bersihkan nilai null menjadi string kosong atau default value
        return array_map(function ($value) {
            return $value ?? '';
        }, $row);
    })
    ->values()
    ->toArray();

    // Update the request with filtered rows
    $request->merge(['rows' => $filteredRows]);

        // 1. Validasi
        try {
        $request->validate([
            'rkhno'                  => 'required|string',
            'mandor_id'              => 'required|exists:mandor,id',
            'tanggal'                => 'required|date',
            'rows'                   => 'required|array|min:1',
            'rows.*.blok'            => 'required|string',
            'rows.*.plot'            => 'required|string',
            'rows.*.nama'            => 'required|string',       // activitycode
            'rows.*.luas'            => 'required|numeric',
            'rows.*.laki_laki'       => 'nullable|integer|min:0',
            'rows.*.perempuan'       => 'nullable|integer|min:0',
            'rows.*.usingvehicle'    => 'required|boolean',
            'rows.*.material_group_id' => 'nullable|integer',
            'rows.*.keterangan'      => 'nullable|string|max:300',
        ]);

        

        DB::transaction(function () use ($request) {
            $companycode = Session::get('companycode');
            $rkhno       = $request->input('rkhno');
            $tanggal     = Carbon::parse($request->input('tanggal'))->format('Y-m-d');

            // 2. Hitung total luas & manpower
            $totalLuas     = collect($request->rows)->sum('luas');
            $totalManpower = collect($request->rows)->sum(function ($row) {
    $laki      = (int) ($row['laki_laki']   ?? 0);
    $perempuan = (int) ($row['perempuan']   ?? 0);
    return $laki + $perempuan;
});

            // 3. Simpan header
            Rkhhdr::create([
                'companycode' => $companycode,
                'rkhno'       => $rkhno,
                'rkhdate'     => $tanggal,
                'totalluas'   => $totalLuas,
                'manpower'    => $totalManpower,
                'mandorid'    => $request->input('mandor_id'),
                'inputby'     => Auth::user()->userid,
                'createdat'   => now(),
            ]);

            // 4. Siapkan data detail
            $details = [];
            foreach ($request->rows as $row) {
                $laki      = (int) ($row['laki_laki']   ?? 0);
    $perempuan = (int) ($row['perempuan']   ?? 0);

                $details[] = [
                    'companycode'         => $companycode,
                    'rkhno'               => $rkhno,
                    'rkhdate'             => $tanggal,
                    'blok'                => $row['blok'],
                    'plot'                => $row['plot'],
                    'activitycode'        => $row['nama'],
                    'luasarea'            => $row['luas'],
                    'jumlahlaki'          => $laki,
                    'jumlahperempuan'     => $perempuan,
                    'jumlahtenagakerja'   => $laki + $perempuan,
                    'usingmaterial'       => !empty($row['material_group_id']) ? 1 : 0,
                    'herbisidagroupid'  => !empty($row['material_group_id'])
                                 ? (int) $row['material_group_id']
                                 : null,
                    'usingvehicle'        => $row['usingvehicle'],
                    'description'         => $row['keterangan'] ?? null,
                ];
            }

            // 5. Insert detail
            DB::table('rkhlst')->insert($details);
        });

        return redirect()->back()->with('success', 'RKH berhasil disimpan.');
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Redirect back dengan old input dan errors
        return redirect()->back()
            ->withInput($request->all())
            ->withErrors($e->validator);
    } catch (\Exception $e) {
        return redirect()->back()
            ->withInput($request->all())
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    public function create()
    {
        $herbisidadosages = new Herbisidadosage;
        $absentenagakerjamodel = new AbsenTenagaKerja;

        $today = Carbon::today();
        $day = $today->format('d');
        $month = $today->format('m');
        $year = $today->format('y');

        // Database table: rkhhdr
        $lastRkh = DB::table('rkhhdr')
            ->whereDate('rkhdate', $today)
            ->where('rkhno', 'like', "RKH{$day}{$month}%" . $year)
            ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc') // SQL Index Start From 1; CAST so the sorting is numeric
            ->first();

        if ($lastRkh) {
            $lastNumber = (int)substr($lastRkh->rkhno, 7, 2); // PHP Index Start From 0
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '01';
        }

        $generatedNoRkh = "RKH{$day}{$month}{$newNumber}{$year}";

        // Database table: mandor
        $mandors = Mandor::orderBy('companycode')->orderBy('id')->get();

        // Database table : activity
        $activities = Activity::with('group')->orderBy('activitycode')->get();

        // Database table : blok
        $bloks = Blok::orderBy('blok')->get();

        // Database table : masterlist
        $masterlist = Masterlist::orderBy('companycode')->orderBy('plot')->get();

        // Database table : absentenagakerja
        $absentenagakerja = $absentenagakerjamodel->getDataAbsenFull(
            session('companycode'),
            $today);

        // Database table : herbisidadosage, herbisidagroup, herbisida
        $herbisidagroups  = $herbisidadosages->getFullHerbisidaGroupData(
            session('companycode')
        );


        return view('input.kerjaharian.rencanakerjaharian.create', [
            'title' => 'Form RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhno' => $generatedNoRkh,
            'mandors'   => $mandors,
            'activities' => $activities,
            'bloks' => $bloks,
            'masterlist' => $masterlist,
            'herbisidagroups' => $herbisidagroups,
            'bloksData' => $bloks,
            'masterlistData' => $masterlist,
            'absentenagakerja' => $absentenagakerja,
            'oldInput' => old(),
        ]);
    }

}