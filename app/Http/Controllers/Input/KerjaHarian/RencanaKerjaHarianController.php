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
        ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
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
                    $query->whereNull('r.approval1flag');
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

        // Filter tanggal - FIXED
        if (empty($allDate)) {
            // Jika tidak show all date
            $dateToFilter = $filterDate ?: Carbon::today()->format('Y-m-d');
            $query->whereDate('r.rkhdate', $dateToFilter);
        }
        // Jika $allDate dicentang, tidak ada filter tanggal

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
                // Hanya ambil rows yang bloknya terisi (blok sebagai trigger)
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

                // FINAL RKH NUMBER GENERATION - Ignore preview, generate fresh with lock
                $rkhno = $this->generateUniqueRkhNoWithLock($tanggal);

                // Hitung total luas & manpower
                $totalLuas = collect($request->rows)->sum('luas');
                $totalManpower = collect($request->rows)->sum(function ($row) {
                    $laki = (int) ($row['laki_laki'] ?? 0);
                    $perempuan = (int) ($row['perempuan'] ?? 0);
                    return $laki + $perempuan;
                });

                // Simpan header
                RkhHdr::create([
                    'companycode' => $companycode,
                    'rkhno'       => $rkhno,
                    'rkhdate'     => $tanggal,
                    'totalluas'   => $totalLuas,
                    'manpower'    => $totalManpower,
                    'mandorid'    => $request->input('mandor_id'),
                    'inputby'     => Auth::user()->userid,
                    'createdat'   => now(),
                ]);

                // Siapkan data detail
                $details = [];
                foreach ($request->rows as $row) {
                    $laki = (int) ($row['laki_laki'] ?? 0);
                    $perempuan = (int) ($row['perempuan'] ?? 0);

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
                        'herbisidagroupid'    => !empty($row['material_group_id'])
                                             ? (int) $row['material_group_id']
                                             : null,
                        'usingvehicle'        => $row['usingvehicle'],
                        'description'         => $row['keterangan'] ?? null,
                    ];
                }

                DB::table('rkhlst')->insert($details);
            });

            // MODERN RESPONSE: Return JSON untuk modal dengan nomor RKH
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
        $activities = Activity::with('group')->orderBy('activitycode')->get();
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
        $activities = Activity::with('group')->orderBy('activitycode')->get();
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
            $request->validate([
                'mandor_id'              => 'required|exists:user,userid',
                'tanggal'                => 'required|date',
                'rows'                   => 'required|array|min:1',
                'rows.*.blok'            => 'required|string',
                'rows.*.plot'            => 'required|string',
                'rows.*.nama'            => 'required|string',
                'rows.*.luas'            => 'required|numeric',
                'rows.*.laki_laki'       => 'nullable|integer|min:0',
                'rows.*.perempuan'       => 'nullable|integer|min:0',
                'rows.*.usingvehicle'    => 'required|boolean',
                'rows.*.material_group_id' => 'nullable|integer',
                'rows.*.keterangan'      => 'nullable|string|max:300',
            ]);

            DB::transaction(function () use ($request, $rkhno) {
                $companycode = Session::get('companycode');
                $tanggal     = Carbon::parse($request->input('tanggal'))->format('Y-m-d');

                // Hitung total luas & manpower
                $totalLuas     = collect($request->rows)->sum('luas');
                $totalManpower = collect($request->rows)->sum(function ($row) {
                    $laki      = (int) ($row['laki_laki']   ?? 0);
                    $perempuan = (int) ($row['perempuan']   ?? 0);
                    return $laki + $perempuan;
                });

                // Update header
                DB::table('rkhhdr')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->update([
                        'rkhdate'     => $tanggal,
                        'totalluas'   => $totalLuas,
                        'manpower'    => $totalManpower,
                        'mandorid'    => $request->input('mandor_id'),
                        'updateby'    => Auth::user()->userid,
                        'updatedat'   => now(),
                    ]);

                // Hapus detail lama
                DB::table('rkhlst')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->delete();

                // Insert detail baru
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
                        'herbisidagroupid'    => !empty($row['material_group_id'])
                                            ? (int) $row['material_group_id']
                                            : null,
                        'usingvehicle'        => $row['usingvehicle'],
                        'description'         => $row['keterangan'] ?? null,
                    ];
                }

                DB::table('rkhlst')->insert($details);
            });

            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('success', 'RKH berhasil diupdate.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($e->validator);
        } catch (\Exception $e) {
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