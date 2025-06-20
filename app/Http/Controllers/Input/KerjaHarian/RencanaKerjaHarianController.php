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
use App\Models\Lkhhdr;
use App\Models\Lkhlst;
use App\Services\LkhGeneratorService;

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

    // Query dasar dengan JOIN ke approval dan activity group
    $query = DB::table('rkhhdr as r')
        ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
        ->leftJoin('approval as app', function($join) use ($companycode) {
            $join->on('r.activitygroup', '=', 'app.activitygroup') // FIXED: ganti 'app.category' jadi 'app.activitygroup'
                 ->where('app.companycode', '=', $companycode);
        })
        ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
        ->where('r.companycode', $companycode)
        ->select([
            'r.*',
            'm.name as mandor_nama',
            'ag.groupname as activity_group_name',
            'app.jumlahapproval',
            'app.idjabatanapproval1',
            'app.idjabatanapproval2',
            'app.idjabatanapproval3',
            // Enhanced approval status logic
            DB::raw('CASE 
                WHEN app.jumlahapproval IS NULL OR app.jumlahapproval = 0 THEN "No Approval Required"
                WHEN r.approval1flag IS NULL AND app.idjabatanapproval1 IS NOT NULL THEN "Waiting Level 1"
                WHEN r.approval1flag = "0" THEN "Declined Level 1"
                WHEN r.approval1flag = "1" AND app.idjabatanapproval2 IS NOT NULL AND r.approval2flag IS NULL THEN "Waiting Level 2"
                WHEN r.approval2flag = "0" THEN "Declined Level 2"
                WHEN r.approval2flag = "1" AND app.idjabatanapproval3 IS NOT NULL AND r.approval3flag IS NULL THEN "Waiting Level 3"
                WHEN r.approval3flag = "0" THEN "Declined Level 3"
                WHEN (app.jumlahapproval = 1 AND r.approval1flag = "1") OR
                     (app.jumlahapproval = 2 AND r.approval1flag = "1" AND r.approval2flag = "1") OR
                     (app.jumlahapproval = 3 AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag = "1") THEN "Approved"
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

    // Enhanced filter approval
    if ($filterApproval) {
        switch ($filterApproval) {
            case 'Approved':
                $query->where(function($q) {
                    $q->where(function($subq) {
                        // Scenario 1: Jumlah approval = 1, approval1flag = 1
                        $subq->where('app.jumlahapproval', 1)
                             ->where('r.approval1flag', '1');
                    })->orWhere(function($subq) {
                        // Scenario 2: Jumlah approval = 2, approval1flag = 1 AND approval2flag = 1
                        $subq->where('app.jumlahapproval', 2)
                             ->where('r.approval1flag', '1')
                             ->where('r.approval2flag', '1');
                    })->orWhere(function($subq) {
                        // Scenario 3: Jumlah approval = 3, semua approval = 1
                        $subq->where('app.jumlahapproval', 3)
                             ->where('r.approval1flag', '1')
                             ->where('r.approval2flag', '1')
                             ->where('r.approval3flag', '1');
                    })->orWhere(function($subq) {
                        // Scenario 4: Tidak ada approval requirement
                        $subq->whereNull('app.jumlahapproval')
                             ->orWhere('app.jumlahapproval', 0);
                    });
                });
                break;
            case 'Waiting':
                $query->where(function($q) {
                    $q->where(function($subq) {
                        // Level 1 waiting
                        $subq->whereNotNull('app.idjabatanapproval1')
                             ->whereNull('r.approval1flag');
                    })->orWhere(function($subq) {
                        // Level 2 waiting
                        $subq->whereNotNull('app.idjabatanapproval2')
                             ->where('r.approval1flag', '1')
                             ->whereNull('r.approval2flag');
                    })->orWhere(function($subq) {
                        // Level 3 waiting
                        $subq->whereNotNull('app.idjabatanapproval3')
                             ->where('r.approval1flag', '1')
                             ->where('r.approval2flag', '1')
                             ->whereNull('r.approval3flag');
                    });
                });
                break;
            case 'Decline':
                $query->where(function($q) {
                    $q->where('r.approval1flag', '0')
                      ->orWhere('r.approval2flag', '0')
                      ->orWhere('r.approval3flag', '0');
                });
                break;
        }
    }

    // Filter status (sama seperti sebelumnya)
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

    // Filter tanggal (sama seperti sebelumnya)
    if (empty($allDate)) {
        $dateToFilter = $filterDate ?: Carbon::today()->format('Y-m-d');
        $query->whereDate('r.rkhdate', $dateToFilter);
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

public function store(Request $request)
{
    // Filter rows yang valid - IMPROVED: Hanya rows dengan blok terisi yang divalidasi
    $filteredRows = collect($request->input('rows', []))
        ->filter(function ($row) {
            return !empty($row['blok']);
        })
        ->map(function ($row) {
            return array_map(function ($value) {
                return $value ?? '';
            }, $row);
        })
        ->values()
        ->toArray();

    $request->merge(['rows' => $filteredRows]);

    try {
        // Custom validation untuk material
        $request->validate([
            'mandor_id'              => 'required|exists:user,userid',
            'tanggal'                => 'required|date',
            'rows'                   => 'required|array|min:1',
            'rows.*.blok'            => 'required|string',
            'rows.*.plot'            => 'required|string',
            'rows.*.nama'            => 'required|string',
            'rows.*.luas'            => 'required|numeric|min:0',
            'rows.*.laki_laki'       => 'required|integer|min:0',
            'rows.*.perempuan'       => 'required|integer|min:0',
            'rows.*.usingvehicle'    => 'required|boolean',
            'rows.*.material_group_id' => 'nullable|integer',
            'rows.*.keterangan'      => 'nullable|string|max:300',
        ], [
            'rows.*.laki_laki.required' => 'Jumlah laki-laki harus diisi (minimal 0)',
            'rows.*.perempuan.required' => 'Jumlah perempuan harus diisi (minimal 0)',
            'rows.*.luas.required' => 'Luas area harus diisi',
            'rows.*.blok.required' => 'Blok harus dipilih',
            'rows.*.plot.required' => 'Plot harus dipilih',
            'rows.*.nama.required' => 'Aktivitas harus dipilih',
            'rows.min' => 'Minimal satu baris harus diisi dengan lengkap',
        ]);

        // CUSTOM VALIDATION untuk material requirement
        $herbisidadosages = new Herbisidadosage;
        $herbisidaData = $herbisidadosages->getFullHerbisidaGroupData(Session::get('companycode'));
        
        foreach ($request->rows as $index => $row) {
            $activityCode = $row['nama'];
            $materialGroupId = $row['material_group_id'] ?? null;
            
            // Check if this activity has material options
            $hasMaterialOptions = collect($herbisidaData)->contains('activitycode', $activityCode);
            
            \Log::info("Material validation - Row " . ($index + 1) . ": Activity={$activityCode}, HasMaterial={$hasMaterialOptions}, GroupId={$materialGroupId}");
            
            if ($hasMaterialOptions && empty($materialGroupId)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "rows.{$index}.material_group_id" => "Baris " . ($index + 1) . ": Grup material harus dipilih untuk aktivitas ini"
                ]);
            }
        }

        $rkhno = null; // Initialize variable
        
        DB::transaction(function () use ($request, &$rkhno) {
            $companycode = Session::get('companycode');
            $tanggal = Carbon::parse($request->input('tanggal'))->format('Y-m-d');

            // FINAL RKH NUMBER GENERATION
            $rkhno = $this->generateUniqueRkhNoWithLock($tanggal);

            // Hitung total luas & manpower
            $totalLuas = collect($request->rows)->sum('luas');
            $totalManpower = collect($request->rows)->sum(function ($row) {
                $laki = (int) ($row['laki_laki'] ?? 0);
                $perempuan = (int) ($row['perempuan'] ?? 0);
                return $laki + $perempuan;
            });

            // TAMBAHAN: Ambil activitygroup dari row pertama yang ada datanya
            $primaryActivityGroup = null;
            foreach ($request->rows as $row) {
                if (!empty($row['nama'])) {
                    $activity = Activity::where('activitycode', $row['nama'])->first();
                    if ($activity && $activity->activitygroup) {
                        $primaryActivityGroup = $activity->activitygroup;
                        break; // Ambil dari aktivitas pertama yang ketemu
                    }
                }
            }

            // TAMBAHAN: Set approval requirements berdasarkan activitygroup
            $approvalData = [];
            if ($primaryActivityGroup) {
                $approvalSetting = DB::table('approval')
                    ->where('companycode', $companycode)
                    ->where('activitygroup', $primaryActivityGroup) // FIXED: ganti 'category' jadi 'activitygroup'
                    ->first();
                
                if ($approvalSetting) {
                    $approvalData = [
                        'jumlahapproval' => $approvalSetting->jumlahapproval,
                        'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
                        'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
                        'approvali3djabatan' => $approvalSetting->idjabatanapproval3, // FIXED: kembali ke nama asli
                    ];
                }
            }

            // Simpan header dengan activitygroup dan approval data
            $headerData = array_merge([
                'companycode' => $companycode,
                'rkhno'       => $rkhno,
                'rkhdate'     => $tanggal,
                'totalluas'   => $totalLuas,
                'manpower'    => $totalManpower,
                'mandorid'    => $request->input('mandor_id'),
                'activitygroup' => $primaryActivityGroup, // TAMBAHAN BARU
                'inputby'     => Auth::user()->userid,
                'createdat'   => now(),
            ], $approvalData);

            RkhHdr::create($headerData);

            // Siapkan data detail (sama seperti sebelumnya)
            $details = [];
            foreach ($request->rows as $row) {
                $laki = (int) ($row['laki_laki'] ?? 0);
                $perempuan = (int) ($row['perempuan'] ?? 0);

                $activity = Activity::where('activitycode', $row['nama'])->first();
                $jenistenagakerja = $activity ? $activity->jenistenagakerja : null;

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
                    'jenistenagakerja'    => $jenistenagakerja,
                    'usingmaterial'       => !empty($row['material_group_id']) ? 1 : 0,
                    'herbisidagroupid'    => !empty($row['material_group_id'])
                                         ? (int) $row['material_group_id']
                                         : null,
                    'usingvehicle'        => $row['usingvehicle'],
                    'description'         => $row['keterangan'] ?? null,
                ];
            }

            DB::table('rkhlst')->insert($details);
        });

        // Response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data berhasil disimpan dengan nomor RKH: <strong>{$rkhno}</strong>",
                'rkhno' => $rkhno,
                'redirect_url' => route('input.kerjaharian.rencanakerjaharian.index')
            ]);
        }

        return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
            ->with('success', 'RKH berhasil disimpan!');

    } catch (\Illuminate\Validation\ValidationException $e) {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan validasi',
                'errors' => $e->errors()
            ], 422);
        }

        return redirect()->back()
            ->withInput($request->all())
            ->withErrors($e->validator);
            
    } catch (\Exception $e) {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()
            ->withInput($request->all())
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    /**
     * ENHANCED: Generate nomor RKH unik dengan advanced database lock untuk mencegah race condition
     */
    private function generateUniqueRkhNoWithLock($date)
    {
        $carbonDate = Carbon::parse($date);
        $day = $carbonDate->format('d');
        $month = $carbonDate->format('m');
        $year = $carbonDate->format('y');
        $companycode = Session::get('companycode');

        return DB::transaction(function () use ($carbonDate, $day, $month, $year, $companycode) {
            // ENHANCED LOCK: Lock dengan WHERE clause yang lebih spesifik
            $lastRkh = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->whereDate('rkhdate', $carbonDate)
                ->where('rkhno', 'like', "RKH{$day}{$month}%" . $year)
                ->lockForUpdate() // Critical: Prevent concurrent access
                ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
                ->first();

            if ($lastRkh) {
                $lastNumber = (int)substr($lastRkh->rkhno, 7, 2);
                $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '01';
            }

            $generatedRkhNo = "RKH{$day}{$month}{$newNumber}{$year}";

            // DOUBLE CHECK: Pastikan nomor belum ada (extra safety)
            $exists = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $generatedRkhNo)
                ->exists();

            if ($exists) {
                // Jika masih ada, generate ulang dengan increment
                $newNumber = str_pad($lastNumber + 2, 2, '0', STR_PAD_LEFT);
                $generatedRkhNo = "RKH{$day}{$month}{$newNumber}{$year}";
            }

            return $generatedRkhNo;
        });
    }

    public function create(Request $request)
    {
        $herbisidadosages = new Herbisidadosage;
        $absentenagakerjamodel = new AbsenTenagaKerja;

        // NEW FLOW: Tanggal dari parameter wajib (dari modal di index)
        $selectedDate = $request->input('date');
        
        if (!$selectedDate) {
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'Silakan pilih tanggal terlebih dahulu');
        }

        $targetDate = Carbon::parse($selectedDate);
        
        // Validasi tanggal range
        $today = Carbon::today();
        $maxDate = Carbon::today()->addDays(7);
        
        if ($targetDate->lt($today) || $targetDate->gt($maxDate)) {
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'Tanggal harus dalam rentang hari ini sampai 7 hari ke depan');
        }

        // Generate nomor RKH berdasarkan tanggal yang dipilih (PREVIEW - untuk hidden input)
        $day = $targetDate->format('d');
        $month = $targetDate->format('m');
        $year = $targetDate->format('y');
        $companycode = Session::get('companycode');

        // Preview nomor RKH (bukan final, akan di-regenerate saat submit)
        $lastRkh = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $targetDate)
            ->where('rkhno', 'like', "RKH{$day}{$month}%" . $year)
            ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
            ->first();

        if ($lastRkh) {
            $lastNumber = (int)substr($lastRkh->rkhno, 7, 2);
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '01';
        }

        $previewRkhNo = "RKH{$day}{$month}{$newNumber}{$year}";

        $companycode = Session::get('companycode');

        // Database queries
        $mandors = User::getMandorByCompany($companycode);
        $activities = Activity::with(['group', 'jenistenagakerja'])->orderBy('activitycode')->get();
        $bloks = Blok::orderBy('blok')->get();
        $masterlist = Masterlist::orderBy('companycode')->orderBy('plot')->get();
        $plots = DB::table('plot')->where('companycode', $companycode)->get(); // Add Plot data
        $absentenagakerja = $absentenagakerjamodel->getDataAbsenFull(
            $companycode,
            $targetDate
        );
        $herbisidagroups = $herbisidadosages->getFullHerbisidaGroupData($companycode);

        return view('input.kerjaharian.rencanakerjaharian.create', [
            'title' => 'Form RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhno' => $previewRkhNo, // Preview untuk hidden input
            'selectedDate' => $targetDate->format('Y-m-d'),
            'mandors' => $mandors,
            'activities' => $activities,
            'bloks' => $bloks,
            'masterlist' => $masterlist,
            'plots' => $plots, // Add plots data
            'herbisidagroups' => $herbisidagroups,
            'bloksData' => $bloks,
            'masterlistData' => $masterlist,
            'plotsData' => $plots, // Add plots data for JS
            'absentenagakerja' => $absentenagakerja,
            'oldInput' => old(),
        ]);
    }

    public function edit($rkhno)
    {
        $companycode = Session::get('companycode');
        
        // Ambil data RKH header
        $rkhHeader = DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'm.name as mandor_nama'
            ])
            ->first();
        
        if (!$rkhHeader) {
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'Data RKH tidak ditemukan');
        }
        
        // Ambil data RKH detail dengan JOIN ke herbisidagroup
        $rkhDetails = DB::table('rkhlst as r')
        ->leftJoin('herbisidagroup as hg', function($join) {
            $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                 ->on('r.activitycode', '=', 'hg.activitycode');
        })
        ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode') // Tambah JOIN ke activity
        ->where('r.companycode', $companycode)
        ->where('r.rkhno', $rkhno)
        ->select([
            'r.*',
            'hg.herbisidagroupname',
            'a.activityname', // Tambah activityname
            'a.jenistenagakerja' // Tambah jenistenagakerja
        ])
        ->get();
        
        // Data untuk dropdown - sama seperti di create
        $herbisidadosages = new Herbisidadosage;
        $absentenagakerjamodel = new AbsenTenagaKerja;
        
        $mandors = User::getMandorByCompany($companycode);
        $activities = Activity::with(['group', 'jenistenagakerja'])->orderBy('activitycode')->get();
        $bloks = Blok::orderBy('blok')->get();
        $masterlist = Masterlist::orderBy('companycode')->orderBy('plot')->get();
        $plots = DB::table('plot')->where('companycode', $companycode)->get(); // Add Plot data
        
        $absentenagakerja = $absentenagakerjamodel->getDataAbsenFull(
            $companycode,
            Carbon::parse($rkhHeader->rkhdate)
        );
        
        $herbisidagroups = $herbisidadosages->getFullHerbisidaGroupData($companycode);
        
        return view('input.kerjaharian.rencanakerjaharian.edit', [
            'title' => 'Edit RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhHeader' => $rkhHeader,
            'rkhDetails' => $rkhDetails,
            'mandors' => $mandors,
            'activities' => $activities,
            'bloks' => $bloks,
            'masterlist' => $masterlist,
            'plots' => $plots, // Add plots data
            'herbisidagroups' => $herbisidagroups,
            'bloksData' => $bloks,
            'masterlistData' => $masterlist,
            'plotsData' => $plots, // Add plots data for JS
            'absentenagakerja' => $absentenagakerja,
            'oldInput' => old(),
        ]);
    }

    public function update(Request $request, $rkhno)
{
    // Same logic as store - filter by blok trigger
    $filteredRows = collect($request->input('rows', []))
        ->filter(function ($row) {
            return !empty($row['blok']);
        })
        ->map(function ($row) {
            return array_map(function ($value) {
                return $value ?? '';
            }, $row);
        })
        ->values()
        ->toArray();

    $request->merge(['rows' => $filteredRows]);

    try {
        // Custom validation untuk material
        $request->validate([
            'mandor_id'              => 'required|exists:user,userid',
            'tanggal'                => 'required|date',
            'rows'                   => 'required|array|min:1',
            'rows.*.blok'            => 'required|string',
            'rows.*.plot'            => 'required|string',
            'rows.*.nama'            => 'required|string',
            'rows.*.luas'            => 'required|numeric|min:0',
            'rows.*.laki_laki'       => 'required|integer|min:0',
            'rows.*.perempuan'       => 'required|integer|min:0',
            'rows.*.usingvehicle'    => 'required|boolean',
            'rows.*.material_group_id' => 'nullable|integer',
            'rows.*.keterangan'      => 'nullable|string|max:300',
        ], [
            'rows.*.laki_laki.required' => 'Jumlah laki-laki harus diisi (minimal 0)',
            'rows.*.perempuan.required' => 'Jumlah perempuan harus diisi (minimal 0)',
            'rows.*.luas.required' => 'Luas area harus diisi',
            'rows.*.blok.required' => 'Blok harus dipilih',
            'rows.*.plot.required' => 'Plot harus dipilih',
            'rows.*.nama.required' => 'Aktivitas harus dipilih',
            'rows.min' => 'Minimal satu baris harus diisi dengan lengkap',
        ]);

        // CUSTOM VALIDATION untuk material requirement
        $herbisidadosages = new Herbisidadosage;
        $herbisidaData = $herbisidadosages->getFullHerbisidaGroupData(Session::get('companycode'));
        
        foreach ($request->rows as $index => $row) {
            $activityCode = $row['nama'];
            $materialGroupId = $row['material_group_id'] ?? null;
            
            // Check if this activity has material options
            $hasMaterialOptions = collect($herbisidaData)->contains('activitycode', $activityCode);
            
            \Log::info("Material validation - Row " . ($index + 1) . ": Activity={$activityCode}, HasMaterial={$hasMaterialOptions}, GroupId={$materialGroupId}");
            
            if ($hasMaterialOptions && empty($materialGroupId)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "rows.{$index}.material_group_id" => "Baris " . ($index + 1) . ": Grup material harus dipilih untuk aktivitas ini"
                ]);
            }
        }

        DB::transaction(function () use ($request, $rkhno) {
            $companycode = Session::get('companycode');
            $tanggal = Carbon::parse($request->input('tanggal'))->format('Y-m-d');

            // Hitung total luas & manpower
            $totalLuas = collect($request->rows)->sum('luas');
            $totalManpower = collect($request->rows)->sum(function ($row) {
                $laki = (int) ($row['laki_laki'] ?? 0);
                $perempuan = (int) ($row['perempuan'] ?? 0);
                return $laki + $perempuan;
            });

            // TAMBAHAN: Ambil activitygroup dari row pertama yang ada datanya (SAMA SEPERTI STORE)
            $primaryActivityGroup = null;
            foreach ($request->rows as $row) {
                if (!empty($row['nama'])) {
                    $activity = Activity::where('activitycode', $row['nama'])->first();
                    if ($activity && $activity->activitygroup) {
                        $primaryActivityGroup = $activity->activitygroup;
                        break; // Ambil dari aktivitas pertama yang ketemu
                    }
                }
            }

            // TAMBAHAN: Set approval requirements berdasarkan activitygroup (SAMA SEPERTI STORE)
            $approvalData = [];
            if ($primaryActivityGroup) {
                $approvalSetting = DB::table('approval')
                    ->where('companycode', $companycode)
                    ->where('activitygroup', $primaryActivityGroup) // FIXED: ganti 'category' jadi 'activitygroup'
                    ->first();
                
                if ($approvalSetting) {
                    $approvalData = [
                        'activitygroup' => $primaryActivityGroup, // TAMBAHAN: Update activitygroup
                        'jumlahapproval' => $approvalSetting->jumlahapproval,
                        'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
                        'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
                        'approvali3djabatan' => $approvalSetting->idjabatanapproval3, // FIXED: kembali ke nama asli
                        // RESET APPROVAL FLAGS karena activity group berubah
                        'approval1flag' => null,
                        'approval2flag' => null,
                        'approval3flag' => null,
                        'approval1date' => null,
                        'approval2date' => null,
                        'approval3date' => null,
                        'approval1userid' => null,
                        'approval2userid' => null,
                        'approval3userid' => null,
                    ];
                }
            }

            // Update header dengan activitygroup dan approval data (ENHANCED)
            $updateData = array_merge([
                'rkhdate'     => $tanggal,
                'totalluas'   => $totalLuas,
                'manpower'    => $totalManpower,
                'mandorid'    => $request->input('mandor_id'),
                'updateby'    => Auth::user()->userid,
                'updatedat'   => now(),
            ], $approvalData);

            DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->update($updateData);

            // Hapus detail lama
            DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();

            // Insert detail baru
            $details = [];
            foreach ($request->rows as $row) {
                $laki = (int) ($row['laki_laki'] ?? 0);
                $perempuan = (int) ($row['perempuan'] ?? 0);

                // FIXED: Get jenistenagakerja from Activity model
                $activity = Activity::where('activitycode', $row['nama'])->first();
                $jenistenagakerja = $activity ? $activity->jenistenagakerja : null;

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
                    'jenistenagakerja'    => $jenistenagakerja, // ADDED: Save jenistenagakerja ID
                    'usingmaterial'       => !empty($row['material_group_id']) ? 1 : 0,
                    'herbisidagroupid'    => !empty($row['material_group_id'])
                                        ? (int) $row['material_group_id']
                                        : null,
                    'usingvehicle'        => $row['usingvehicle'],
                    'description'         => $row['keterangan'] ?? null,
                ];
            }

            DB::table('rkhlst')->insert($details);
        });

        // Response untuk AJAX atau redirect biasa
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "RKH berhasil diupdate!",
                'rkhno' => $rkhno,
                'redirect_url' => route('input.kerjaharian.rencanakerjaharian.index')
            ]);
        }

        return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
            ->with('success', 'RKH berhasil diupdate!');
            
    } catch (\Illuminate\Validation\ValidationException $e) {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan validasi',
                'errors' => $e->errors()
            ], 422);
        }

        return redirect()->back()
            ->withInput($request->all())
            ->withErrors($e->validator);
            
    } catch (\Exception $e) {
        \Log::error("Update RKH Error: " . $e->getMessage(), [
            'rkhno' => $rkhno,
            'trace' => $e->getTraceAsString()
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()
            ->withInput($request->all())
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
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

    public function getDTHData(Request $request)
{
    $date = $request->query('date', date('Y-m-d'));
    $companycode = Session::get('companycode');
    
    \Log::info("DTH Debug - Date: {$date}, Company: {$companycode}");
    
    try {
        // Query untuk mendapatkan data RKH yang sudah approved pada tanggal tertentu
        $query = DB::table('rkhhdr as h')
            ->join('rkhlst as l', 'h.rkhno', '=', 'l.rkhno')
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->where('h.approval1flag', '1') // Hanya yang sudah approved
            ->select([
                'l.rkhno',
                'l.blok',
                'l.plot',
                'l.luasarea',
                'l.jumlahlaki',
                'l.jumlahperempuan',
                'l.jumlahtenagakerja',
                'l.jenistenagakerja',
                'l.description',
                'u.name as mandor_nama',
                'a.activityname'
            ]);

        $allData = $query->get();
        
        \Log::info("DTH Debug - Total records: " . $allData->count());
        \Log::info("DTH Debug - Sample data: " . $allData->take(2)->toJson());

        // Pisahkan data berdasarkan jenistenagakerja
        // 1 = Harian, 2 = Borongan, operator dan helper diabaikan
        $harianData = $allData->where('jenistenagakerja', 1)->values()->toArray();
        $boronganData = $allData->where('jenistenagakerja', 2)->values()->toArray();

        \Log::info("DTH Debug - Harian count: " . count($harianData) . ", Borongan count: " . count($boronganData));

        return response()->json([
            'success' => true,
            'harian' => $harianData,
            'borongan' => $boronganData,
            'date' => $date,
            'debug' => [
                'total_records' => $allData->count(),
                'harian_count' => count($harianData),
                'borongan_count' => count($boronganData)
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error("DTH Error: " . $e->getMessage());
        \Log::error("DTH Error Trace: " . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil data DTH: ' . $e->getMessage()
        ], 500);
    }
}

public function showDTHReport(Request $request)
{
    $date = $request->query('date', date('Y-m-d'));
    
    return view('input.kerjaharian.rencanakerjaharian.dth-report', [
        'date' => $date
    ]);
}

    public function generateDTH(Request $request)
{
    $request->validate([
        'date' => 'required|date'
    ]);

    $date = $request->date;
    
    // Redirect ke halaman DTH report dengan parameter tanggal
    $url = route('input.kerjaharian.rencanakerjaharian.dth-report', ['date' => $date]);
    
    return response()->json([
        'success' => true,
        'message' => 'Membuka laporan DTH...',
        'redirect_url' => $url
    ]);
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

 /**
     * Get pending approvals for current user
     */
    public function getPendingApprovals(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            
            if (!$currentUser || !$currentUser->idjabatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki jabatan yang valid'
                ]);
            }

            // Query RKH yang menunggu approval dari user saat ini
            $pendingRKH = DB::table('rkhhdr as r')
                ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
                ->leftJoin('approval as app', function($join) use ($companycode) {
                    $join->on('r.activitygroup', '=', 'app.activitygroup') // FIXED: ganti 'app.category' jadi 'app.activitygroup'
                         ->where('app.companycode', '=', $companycode);
                })
                ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
                ->leftJoin('jabatan as j1', 'app.idjabatanapproval1', '=', 'j1.idjabatan')
                ->leftJoin('jabatan as j2', 'app.idjabatanapproval2', '=', 'j2.idjabatan')
                ->leftJoin('jabatan as j3', 'app.idjabatanapproval3', '=', 'j3.idjabatan')
                ->where('r.companycode', $companycode)
                ->where(function($query) use ($currentUser) {
                    $query->where(function($q) use ($currentUser) {
                        // Level 1 approval
                        $q->where('app.idjabatanapproval1', $currentUser->idjabatan)
                          ->whereNull('r.approval1flag');
                    })->orWhere(function($q) use ($currentUser) {
                        // Level 2 approval
                        $q->where('app.idjabatanapproval2', $currentUser->idjabatan)
                          ->where('r.approval1flag', '1')
                          ->whereNull('r.approval2flag');
                    })->orWhere(function($q) use ($currentUser) {
                        // Level 3 approval
                        $q->where('app.idjabatanapproval3', $currentUser->idjabatan)
                          ->where('r.approval1flag', '1')
                          ->where('r.approval2flag', '1')
                          ->whereNull('r.approval3flag');
                    });
                })
                ->select([
                    'r.*',
                    'm.name as mandor_nama',
                    'ag.groupname as activity_group_name',
                    'app.jumlahapproval',
                    'app.idjabatanapproval1',
                    'app.idjabatanapproval2',
                    'app.idjabatanapproval3',
                    'j1.namajabatan as jabatan1_name',
                    'j2.namajabatan as jabatan2_name',
                    'j3.namajabatan as jabatan3_name',
                    DB::raw('CASE 
                        WHEN app.idjabatanapproval1 = '.$currentUser->idjabatan.' AND r.approval1flag IS NULL THEN 1
                        WHEN app.idjabatanapproval2 = '.$currentUser->idjabatan.' AND r.approval1flag = "1" AND r.approval2flag IS NULL THEN 2
                        WHEN app.idjabatanapproval3 = '.$currentUser->idjabatan.' AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag IS NULL THEN 3
                        ELSE 0
                    END as approval_level')
                ])
                ->orderBy('r.rkhdate', 'desc')
                ->get();

            // Format data
            $formattedData = $pendingRKH->map(function($rkh) {
                return [
                    'rkhno' => $rkh->rkhno,
                    'rkhdate' => $rkh->rkhdate,
                    'rkhdate_formatted' => Carbon::parse($rkh->rkhdate)->format('d/m/Y'),
                    'mandor_nama' => $rkh->mandor_nama,
                    'activity_group_name' => $rkh->activity_group_name ?? 'Unknown',
                    'approval_level' => $rkh->approval_level,
                    'total_luas' => $rkh->totalluas,
                    'manpower' => $rkh->manpower
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'user_info' => [
                    'userid' => $currentUser->userid,
                    'name' => $currentUser->name,
                    'idjabatan' => $currentUser->idjabatan,
                    'jabatan_name' => $this->getJabatanName($currentUser->idjabatan)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting pending approvals: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process approval untuk RKH dengan auto-generate LKH
     */
    public function processApproval(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            $rkhno = $request->rkhno;
            $action = $request->action;
            $level = $request->level;

            if (!$currentUser || !$currentUser->idjabatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki jabatan yang valid'
                ]);
            }

            // Cek apakah RKH ada dan user berhak approve
            $rkh = DB::table('rkhhdr as r')
                ->leftJoin('approval as app', function($join) use ($companycode) {
                    $join->on('r.activitygroup', '=', 'app.activitygroup')
                         ->where('app.companycode', '=', $companycode);
                })
                ->where('r.companycode', $companycode)
                ->where('r.rkhno', $rkhno)
                ->select(['r.*', 'app.*'])
                ->first();

            if (!$rkh) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKH tidak ditemukan'
                ]);
            }

            // Validasi apakah user berhak approve di level ini
            $canApprove = false;
            $approvalField = '';
            $approvalDateField = '';
            $approvalUserField = '';

            switch ($level) {
                case 1:
                    if ($rkh->idjabatanapproval1 == $currentUser->idjabatan && is_null($rkh->approval1flag)) {
                        $canApprove = true;
                        $approvalField = 'approval1flag';
                        $approvalDateField = 'approval1date';
                        $approvalUserField = 'approval1userid';
                    }
                    break;
                case 2:
                    if ($rkh->idjabatanapproval2 == $currentUser->idjabatan && 
                        $rkh->approval1flag == '1' && is_null($rkh->approval2flag)) {
                        $canApprove = true;
                        $approvalField = 'approval2flag';
                        $approvalDateField = 'approval2date';
                        $approvalUserField = 'approval2userid';
                    }
                    break;
                case 3:
                    if ($rkh->idjabatanapproval3 == $currentUser->idjabatan && 
                        $rkh->approval1flag == '1' && $rkh->approval2flag == '1' && is_null($rkh->approval3flag)) {
                        $canApprove = true;
                        $approvalField = 'approval3flag';
                        $approvalDateField = 'approval3date';
                        $approvalUserField = 'approval3userid';
                    }
                    break;
            }

            if (!$canApprove) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak untuk melakukan approval pada level ini'
                ]);
            }

            // Update approval
            $approvalValue = $action === 'approve' ? '1' : '0';
            
            DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->update([
                    $approvalField => $approvalValue,
                    $approvalDateField => now(),
                    $approvalUserField => $currentUser->userid,
                    'updateby' => $currentUser->userid,
                    'updatedat' => now()
                ]);

            $responseMessage = 'RKH berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // AUTO-GENERATE LKH JIKA SUDAH FULLY APPROVED
            if ($action === 'approve') {
                $updatedRkh = DB::table('rkhhdr')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->first();

                if ($this->isRkhFullyApproved($updatedRkh)) {
                    try {
                        $lkhGenerator = new LkhGeneratorService();
                        $lkhResult = $lkhGenerator->generateLkhFromRkh($rkhno);
                        
                        if ($lkhResult['success']) {
                            $responseMessage .= '. LKH telah di-generate otomatis (' . $lkhResult['total_lkh'] . ' LKH)';
                            
                            \Log::info("Auto-generated LKH for approved RKH {$rkhno}", [
                                'generated_lkh' => $lkhResult['generated_lkh'],
                                'approved_by' => $currentUser->userid,
                                'approval_level' => $level
                            ]);
                        } else {
                            // Jika gagal generate LKH, tetap berhasil approve tapi kasih warning
                            $responseMessage .= '. WARNING: Gagal auto-generate LKH - ' . $lkhResult['message'];
                            
                            \Log::warning("Failed to auto-generate LKH for approved RKH {$rkhno}", [
                                'error' => $lkhResult['message'],
                                'approved_by' => $currentUser->userid
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Log error tapi tidak gagalkan approval
                        \Log::error("Exception during LKH auto-generation for RKH {$rkhno}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        $responseMessage .= '. WARNING: Error saat auto-generate LKH';
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => $responseMessage
            ]);

        } catch (\Exception $e) {
            \Log::error("Error processing approval: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method untuk check apakah RKH sudah fully approved
     */
    private function isRkhFullyApproved($rkh)
    {
        // Jika tidak ada requirement approval, anggap sudah approved
        if (!$rkh->jumlahapproval || $rkh->jumlahapproval == 0) {
            return true;
        }

        // Check berdasarkan jumlah approval yang diperlukan
        switch ($rkh->jumlahapproval) {
            case 1:
                return $rkh->approval1flag === '1';
            case 2:
                return $rkh->approval1flag === '1' && $rkh->approval2flag === '1';
            case 3:
                return $rkh->approval1flag === '1' && 
                       $rkh->approval2flag === '1' && 
                       $rkh->approval3flag === '1';
            default:
                return false;
        }
    }

    /**
     * Manual generate LKH (untuk keperluan khusus)
     */
    public function manualGenerateLkh(Request $request, $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Validasi RKH exists dan milik company yang benar
            $rkh = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->first();
                
            if (!$rkh) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKH tidak ditemukan'
                ]);
            }

            $lkhGenerator = new LkhGeneratorService();
            $result = $lkhGenerator->generateLkhFromRkh($rkhno);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Manual generate LKH error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approval detail for specific RKH
     */
    public function getApprovalDetail($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Get RKH with approval info
            $rkh = DB::table('rkhhdr as r')
                ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
                ->leftJoin('approval as app', function($join) use ($companycode) {
                    $join->on('r.activitygroup', '=', 'app.activitygroup')
                         ->where('app.companycode', '=', $companycode);
                })
                ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
                ->leftJoin('user as u1', 'r.approval1userid', '=', 'u1.userid')
                ->leftJoin('user as u2', 'r.approval2userid', '=', 'u2.userid')
                ->leftJoin('user as u3', 'r.approval3userid', '=', 'u3.userid')
                ->leftJoin('jabatan as j1', 'app.idjabatanapproval1', '=', 'j1.idjabatan')
                ->leftJoin('jabatan as j2', 'app.idjabatanapproval2', '=', 'j2.idjabatan')
                ->leftJoin('jabatan as j3', 'app.idjabatanapproval3', '=', 'j3.idjabatan')
                ->where('r.companycode', $companycode)
                ->where('r.rkhno', $rkhno)
                ->select([
                    'r.*',
                    'm.name as mandor_nama',
                    'ag.groupname as activity_group_name',
                    'app.jumlahapproval',
                    'app.idjabatanapproval1',
                    'app.idjabatanapproval2', 
                    'app.idjabatanapproval3',
                    'u1.name as approval1_user_name',
                    'u2.name as approval2_user_name',
                    'u3.name as approval3_user_name',
                    'j1.namajabatan as jabatan1_name',
                    'j2.namajabatan as jabatan2_name',
                    'j3.namajabatan as jabatan3_name'
                ])
                ->first();

            if (!$rkh) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKH tidak ditemukan'
                ]);
            }

            // Build approval levels
            $levels = [];
            
            for ($i = 1; $i <= 3; $i++) {
                $jabatanId = $rkh->{"idjabatanapproval{$i}"};
                if (!$jabatanId) continue;

                $flagField = "approval{$i}flag";
                $dateField = "approval{$i}date";
                $userField = "approval{$i}_user_name";
                $jabatanField = "jabatan{$i}_name";

                $flag = $rkh->$flagField;
                $status = 'waiting';
                $statusText = 'Waiting';

                if ($flag === '1') {
                    $status = 'approved';
                    $statusText = 'Approved';
                } elseif ($flag === '0') {
                    $status = 'declined';
                    $statusText = 'Declined';
                }

                $levels[] = [
                    'level' => $i,
                    'jabatan_name' => $rkh->$jabatanField ?? 'Unknown',
                    'status' => $status,
                    'status_text' => $statusText,
                    'user_name' => $rkh->$userField ?? null,
                    'date_formatted' => $rkh->$dateField ? Carbon::parse($rkh->$dateField)->format('d/m/Y H:i') : null
                ];
            }

            $formattedData = [
                'rkhno' => $rkh->rkhno,
                'rkhdate' => $rkh->rkhdate,
                'rkhdate_formatted' => Carbon::parse($rkh->rkhdate)->format('d/m/Y'),
                'mandor_nama' => $rkh->mandor_nama,
                'activity_group_name' => $rkh->activity_group_name ?? 'Unknown',
                'jumlah_approval' => $rkh->jumlahapproval ?? 0,
                'levels' => $levels
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method untuk mendapatkan nama jabatan
     */
    private function getJabatanName($idjabatan)
    {
        $jabatan = DB::table('jabatan')->where('idjabatan', $idjabatan)->first();
        return $jabatan ? $jabatan->namajabatan : 'Unknown';
    }

    /**
     * Get LKH data for specific RKH - UPDATED VERSION
     */
    public function getLKHData($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Ambil data LKH berdasarkan RKH dengan join ke activity
            $lkhList = DB::table('lkhhdr as h')
                ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
                ->where('h.companycode', $companycode)
                ->where('h.rkhno', $rkhno)
                ->select([
                    'h.lkhno',
                    'h.activitycode',
                    'a.activityname',
                    'h.blok',
                    'h.plot',
                    'h.jenistenagakerja',
                    'h.status',
                    'h.lkhdate',
                    'h.totalworkers',
                    'h.totalhasil',
                    'h.totalsisa',
                    'h.createdat'
                ])
                ->orderBy('h.lkhno')
                ->get();

            // Format data untuk modal
            $formattedData = $lkhList->map(function($lkh) {
                return [
                    'lkhno' => $lkh->lkhno,
                    'activity' => $lkh->activitycode . ' - ' . ($lkh->activityname ?? 'Unknown Activity'),
                    'location' => "Blok {$lkh->blok}, Plot {$lkh->plot}",
                    'jenis_tenaga' => $lkh->jenistenagakerja == 1 ? 'Harian' : 'Borongan',
                    'status' => $lkh->status,
                    'workers' => $lkh->totalworkers,
                    'hasil' => $lkh->totalhasil,
                    'sisa' => $lkh->totalsisa,
                    'date_formatted' => Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
                    'created_at' => $lkh->createdat ? Carbon::parse($lkh->createdat)->format('d/m/Y H:i') : '-',
                    'view_url' => route('input.kerjaharian.rencanakerjaharian.showLKH', $lkh->lkhno),
                    'edit_url' => route('input.kerjaharian.rencanakerjaharian.editLKH', $lkh->lkhno)
                ];
            });

            // Tambahkan informasi apakah RKH sudah approved dan bisa generate LKH
            $canGenerateLkh = false;
            $generateMessage = '';
            
            $rkhData = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->first();
                
            if ($rkhData) {
                if ($this->isRkhFullyApproved($rkhData)) {
                    if ($lkhList->isEmpty()) {
                        $canGenerateLkh = true;
                        $generateMessage = 'RKH sudah approved, LKH bisa di-generate';
                    } else {
                        $generateMessage = 'LKH sudah pernah di-generate';
                    }
                } else {
                    $generateMessage = 'RKH belum fully approved';
                }
            }

            return response()->json([
                'success' => true,
                'lkh_data' => $formattedData,
                'rkhno' => $rkhno,
                'can_generate_lkh' => $canGenerateLkh,
                'generate_message' => $generateMessage,
                'total_lkh' => $lkhList->count()
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting LKH data: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show LKH Report
     */
    public function showLKH($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Ambil data LKH Header dengan JOIN ke tabel terkait
            $lkhData = DB::table('lkhhdr as h')
                ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
                ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
                ->leftJoin('approval as app', function($join) use ($companycode) {
                    $join->on('h.activitycode', '=', 'app.activitygroup') // Sesuaikan dengan struktur approval
                         ->where('app.companycode', '=', $companycode);
                })
                ->where('h.companycode', $companycode)
                ->where('h.lkhno', $lkhno)
                ->select([
                    'h.*',
                    'm.name as mandornama',
                    'a.activityname',
                    'app.jumlahapproval',
                    'app.idjabatanapproval1',
                    'app.idjabatanapproval2',
                    'app.idjabatanapproval3'
                ])
                ->first();

            if (!$lkhData) {
                return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }

            // Ambil data LKH Detail (workers)
            $lkhDetails = DB::table('lkhlst')
                ->where('lkhno', $lkhno)
                ->orderBy('workersequence')
                ->get();

            // Ambil data approval settings untuk signature section
            $approvals = new \stdClass();
            if ($lkhData->jumlahapproval > 0) {
                $jabatanData = DB::table('jabatan')
                    ->whereIn('idjabatan', array_filter([
                        $lkhData->idjabatanapproval1,
                        $lkhData->idjabatanapproval2,
                        $lkhData->idjabatanapproval3
                    ]))
                    ->pluck('namajabatan', 'idjabatan');

                $approvals->jabatan1name = $jabatanData[$lkhData->idjabatanapproval1] ?? null;
                $approvals->jabatan2name = $jabatanData[$lkhData->idjabatanapproval2] ?? null;
                $approvals->jabatan3name = $jabatanData[$lkhData->idjabatanapproval3] ?? null;
                $approvals->jabatan4name = null; // Placeholder for consistency
            }

            return view('input.kerjaharian.rencanakerjaharian.lkh-report', [
                'title' => 'Laporan Kegiatan Harian (LKH)',
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
                'lkhData' => $lkhData,
                'lkhDetails' => $lkhDetails,
                'approvals' => $approvals
            ]);

        } catch (\Exception $e) {
            \Log::error("Error showing LKH: " . $e->getMessage());
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan LKH: ' . $e->getMessage());
        }
    }

    /**
     * Edit LKH (placeholder untuk implementasi nanti)
     */
    public function editLKH($lkhno)
    {
        // TODO: Implementasi edit LKH
        return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
            ->with('info', 'Fitur edit LKH belum tersedia');
    }

    /**
     * Update status RKH
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'status' => 'required|string'
        ]);

        try {
            $companycode = Session::get('companycode');
            
            $updated = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $request->rkhno)
                ->update([
                    'status' => $request->status,
                    'updateby' => Auth::user()->userid,
                    'updatedat' => now()
                ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status RKH berhasil diupdate'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'RKH tidak ditemukan'
                ], 404);
            }

        } catch (\Exception $e) {
            \Log::error("Error updating RKH status: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status: ' . $e->getMessage()
            ], 500);
        }
    }
}