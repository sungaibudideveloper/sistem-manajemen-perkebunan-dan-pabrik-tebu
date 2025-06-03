<?php

namespace App\Http\Controllers\Input\KerjaHarian;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\User;
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
        $search = $request->input('search');
        $filterApproval = $request->input('filter_approval');
        $filterStatus = $request->input('filter_status');
        $filterDate = $request->input('filter_date');
        $allDate = $request->input('all_date');
        
        $companycode = Session::get('companycode');

        // Query dasar
        $query = DB::table('rkhhdr as r')
        ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid') // <- ini diperbaiki
        ->where('r.companycode', $companycode)
        ->select([
            'r.*',
            'm.name as mandor_nama',
            DB::raw('CASE 
                WHEN r.approval1flag IS NULL THEN "Waiting"
                WHEN r.approval1flag = "1" THEN "Approved" 
                WHEN r.approval1flag = "0" THEN "Decline"
                ELSE "Waiting"
            END as approval_status'),
            DB::raw('CASE 
                WHEN r.status = "Done" THEN "Done"
                ELSE "On Progress"
            END as current_status')
        ]);

        // Filter pencarian
        if ($search) {
            $query->where('r.rkhno', 'like', '%' . $search . '%');
        }

        // Filter approval
        if ($filterApproval) {
            switch ($filterApproval) {
                case 'Approved':
                    $query->where('r.approval1flag', '1');
                    break;
                case 'Waiting':
                    $query->whereNull('r.approva1flagl');
                    break;
                case 'Decline':
                    $query->where('r.approval1flag', '0');
                    break;
            }
        }

        // Filter status
        if ($filterStatus) {
            if ($filterStatus == 'Done') {
                $query->where('r.status', 'Done');
            } else {
                $query->where(function($q) {
                    $q->where('r.status', '!=', 'Done')
                      ->orWhereNull('r.status');
                });
            }
        }

        // Filter tanggal
        if (!$allDate && $filterDate) {
            $query->whereDate('r.rkhdate', $filterDate);
        } elseif (!$allDate && !$filterDate) {
            // Default ke hari ini jika tidak ada filter tanggal dan tidak show all
            $query->whereDate('r.rkhdate', Carbon::today());
        }

        // Hitung total kegiatan per RKH dari tabel rkhlst
        $rkhActivities = DB::table('rkhlst')
            ->select('rkhno', DB::raw('COUNT(*) as total_activities'))
            ->where('companycode', $companycode)
            ->groupBy('rkhno');

        // Join dengan query utama
        $query->leftJoinSub($rkhActivities, 'activities', function ($join) {
            $join->on('r.rkhno', '=', 'activities.rkhno');
        });

        // Urutkan berdasarkan tanggal terbaru
        $query->orderBy('r.rkhdate', 'desc')
              ->orderBy('r.rkhno', 'desc');

        $rkhData = $query->paginate($perPage);

        // Data untuk modal absen
        $absentenagakerjamodel = new AbsenTenagaKerja;
        $absentenagakerja = $absentenagakerjamodel->getDataAbsenFull(
            $companycode,
            Carbon::parse($filterDate ?? Carbon::today())
        );


        return view('input.kerjaharian.rencanakerjaharian.index', [
            'title' => 'Rencana Kerja Harian',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'perPage' => $perPage,
            'search' => $search,
            'filterApproval' => $filterApproval,
            'filterStatus' => $filterStatus,
            'filterDate' => $filterDate,
            'allDate' => $allDate,
            'rkhData' => $rkhData,
            'absentenagakerja' => $absentenagakerja,
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'status' => 'required|in:Done'
        ]);

        $companycode = Session::get('companycode');
        
        try {
            DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $request->rkhno)
                ->update([
                    'status' => $request->status,
                    'updateby' => Auth::user()->userid,
                    'updatedat' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status: ' . $e->getMessage()
            ], 500);
        }
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
            'mandor_id'              => 'required|exists:user,userid',
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
        $mandors = User::getMandorByCompany(session('companycode'));

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

    public function destroy($rkhno)
    {
        $companycode = Session::get('companycode');
        
        try {
            DB::beginTransaction();
            
            // Delete from rkhlst first (jika ada)
            DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            // Delete from rkhhdr
            $deleted = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            if ($deleted) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'RKH berhasil dihapus'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'RKH tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus RKH: ' . $e->getMessage()
            ], 500);
        }
    }
    /*
    public function getLKHData($rkhno)
    {
        $companycode = Session::get('companycode');
        
        try {
            // Ambil data LKH yang sudah ada untuk RKH ini
            $lkhData = DB::table('lkhhdr as l')
                ->leftJoin('activity as a', 'l.activityid', '=', 'a.id')
                ->where('l.companycode', $companycode)
                ->where('l.rkhno', $rkhno)
                ->select([
                    'l.lkhno',
                    'a.activity',
                    'l.rkhno'
                ])
                ->get()
                ->map(function ($item) {
                    $item->check_url = route('input.kerjaharian.laporankerjaharian.show', $item->lkhno);
                    return $item;
                });

            return response()->json([
                'success' => true,
                'lkh_data' => $lkhData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data LKH: ' . $e->getMessage()
            ], 500);
        }
    }*/

    public function generateDTH(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $companycode = Session::get('companycode');
        $date = $request->date;
        
        try {
            // Logika generate DTH - sesuaikan dengan kebutuhan bisnis Anda
            // Contoh: ambil semua RKH yang approved pada tanggal tersebut
            $rkhData = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->whereDate('rkhdate', $date)
                ->where('approval', '1') // hanya yang approved
                ->where('status', 'Done') // hanya yang sudah selesai
                ->get();

            if ($rkhData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada RKH yang approved dan selesai pada tanggal tersebut'
                ]);
            }

            // Proses generate DTH di sini
            // Ini hanya contoh, sesuaikan dengan logika bisnis Anda
            
            return response()->json([
                'success' => true,
                'message' => 'DTH berhasil di-generate untuk ' . $rkhData->count() . ' RKH'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate DTH: ' . $e->getMessage()
            ], 500);
        }
    }


public function loadAbsenByDate(Request $request)
{
    $date = $request->query('date', date('Y-m-d'));
    $mandorId = $request->query('mandor_id');
    $companycode = Session::get('companycode');
    
    $absentenagakerjamodel = new AbsenTenagaKerja;
    $absenData = $absentenagakerjamodel->getDataAbsenFull($companycode, Carbon::parse($date), $mandorId);
    $mandorList = $absentenagakerjamodel->getMandorList($companycode, Carbon::parse($date));

    return response()->json([
        'success' => true,
        'data' => $absenData,
        'mandor_list' => $mandorList
    ]);
}

}