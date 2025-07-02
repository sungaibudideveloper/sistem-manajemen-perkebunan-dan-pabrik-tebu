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
                $join->on('r.activitygroup', '=', 'app.activitygroup')
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

        // Apply filters
        if ($search) {
            $query->where('r.rkhno', 'like', '%' . $search . '%');
        }

        if ($filterApproval) {
            switch ($filterApproval) {
                case 'Approved':
                    $query->where(function($q) {
                        $q->where(function($subq) {
                            $subq->where('app.jumlahapproval', 1)->where('r.approval1flag', '1');
                        })->orWhere(function($subq) {
                            $subq->where('app.jumlahapproval', 2)->where('r.approval1flag', '1')->where('r.approval2flag', '1');
                        })->orWhere(function($subq) {
                            $subq->where('app.jumlahapproval', 3)->where('r.approval1flag', '1')->where('r.approval2flag', '1')->where('r.approval3flag', '1');
                        })->orWhere(function($subq) {
                            $subq->whereNull('app.jumlahapproval')->orWhere('app.jumlahapproval', 0);
                        });
                    });
                    break;
                case 'Waiting':
                    $query->where(function($q) {
                        $q->where(function($subq) {
                            $subq->whereNotNull('app.idjabatanapproval1')->whereNull('r.approval1flag');
                        })->orWhere(function($subq) {
                            $subq->whereNotNull('app.idjabatanapproval2')->where('r.approval1flag', '1')->whereNull('r.approval2flag');
                        })->orWhere(function($subq) {
                            $subq->whereNotNull('app.idjabatanapproval3')->where('r.approval1flag', '1')->where('r.approval2flag', '1')->whereNull('r.approval3flag');
                        });
                    });
                    break;
                case 'Decline':
                    $query->where(function($q) {
                        $q->where('r.approval1flag', '0')->orWhere('r.approval2flag', '0')->orWhere('r.approval3flag', '0');
                    });
                    break;
            }
        }

        if ($filterStatus) {
            if ($filterStatus == 'Done') {
                $query->where('r.status', 'Done');
            } else {
                $query->where(function($q) {
                    $q->where('r.status', '!=', 'Done')->orWhereNull('r.status');
                });
            }
        }

        if (empty($allDate)) {
            $dateToFilter = $filterDate ?: Carbon::today()->format('Y-m-d');
            $query->whereDate('r.rkhdate', $dateToFilter);
        }

        $query->orderBy('r.rkhdate', 'desc')->orderBy('r.rkhno', 'desc');
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
                'rows.*.luas'            => 'required|numeric|min:0',
                'rows.*.laki_laki'       => 'required|integer|min:0',
                'rows.*.perempuan'       => 'required|integer|min:0',
                'rows.*.usingvehicle'    => 'required|boolean',
                'rows.*.material_group_id' => 'nullable|integer',
                'rows.*.keterangan'      => 'nullable|string|max:300',
            ]);

            $rkhno = null;
            
            DB::transaction(function () use ($request, &$rkhno) {
                $companycode = Session::get('companycode');
                $tanggal = Carbon::parse($request->input('tanggal'))->format('Y-m-d');

                $rkhno = $this->generateUniqueRkhNoWithLock($tanggal);

                $totalLuas = collect($request->rows)->sum('luas');
                $totalManpower = collect($request->rows)->sum(function ($row) {
                    return ((int) ($row['laki_laki'] ?? 0)) + ((int) ($row['perempuan'] ?? 0));
                });

                $primaryActivityGroup = null;
                foreach ($request->rows as $row) {
                    if (!empty($row['nama'])) {
                        $activity = Activity::where('activitycode', $row['nama'])->first();
                        if ($activity && $activity->activitygroup) {
                            $primaryActivityGroup = $activity->activitygroup;
                            break;
                        }
                    }
                }

                $approvalData = [];
                if ($primaryActivityGroup) {
                    $approvalSetting = DB::table('approval')
                        ->where('companycode', $companycode)
                        ->where('activitygroup', $primaryActivityGroup)
                        ->first();
                    
                    if ($approvalSetting) {
                        $approvalData = [
                            'jumlahapproval' => $approvalSetting->jumlahapproval,
                            'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
                            'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
                            'approval3idjabatan' => $approvalSetting->idjabatanapproval3,
                        ];
                    }
                }

                $headerData = array_merge([
                    'companycode' => $companycode,
                    'rkhno'       => $rkhno,
                    'rkhdate'     => $tanggal,
                    'totalluas'   => $totalLuas,
                    'manpower'    => $totalManpower,
                    'mandorid'    => $request->input('mandor_id'),
                    'activitygroup' => $primaryActivityGroup,
                    'inputby'     => Auth::user()->userid,
                    'createdat'   => now(),
                ], $approvalData);

                RkhHdr::create($headerData);

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
                        'herbisidagroupid'    => !empty($row['material_group_id']) ? (int) $row['material_group_id'] : null,
                        'usingvehicle'        => $row['usingvehicle'],
                        'description'         => $row['keterangan'] ?? null,
                    ];
                }

                DB::table('rkhlst')->insert($details);
            });

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

        } catch (\Exception $e) {
            \Log::error("Store RKH Error: " . $e->getMessage());
            
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

    private function generateUniqueRkhNoWithLock($date)
    {
        $carbonDate = Carbon::parse($date);
        $day = $carbonDate->format('d');
        $month = $carbonDate->format('m');
        $year = $carbonDate->format('y');
        $companycode = Session::get('companycode');

        return DB::transaction(function () use ($carbonDate, $day, $month, $year, $companycode) {
            $lastRkh = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->whereDate('rkhdate', $carbonDate)
                ->where('rkhno', 'like', "RKH{$day}{$month}%" . $year)
                ->lockForUpdate()
                ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
                ->first();

            if ($lastRkh) {
                $lastNumber = (int)substr($lastRkh->rkhno, 7, 2);
                $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '01';
            }

            return "RKH{$day}{$month}{$newNumber}{$year}";
        });
    }

    public function create(Request $request)
    {
        $selectedDate = $request->input('date');
        
        if (!$selectedDate) {
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'Silakan pilih tanggal terlebih dahulu');
        }

        $targetDate = Carbon::parse($selectedDate);
        $today = Carbon::today();
        $maxDate = Carbon::today()->addDays(7);
        
        if ($targetDate->lt($today) || $targetDate->gt($maxDate)) {
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'Tanggal harus dalam rentang hari ini sampai 7 hari ke depan');
        }

        $day = $targetDate->format('d');
        $month = $targetDate->format('m');
        $year = $targetDate->format('y');
        $companycode = Session::get('companycode');

        $lastRkh = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $targetDate)
            ->where('rkhno', 'like', "RKH{$day}{$month}%" . $year)
            ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
            ->first();

        $newNumber = $lastRkh ? str_pad(((int)substr($lastRkh->rkhno, 7, 2)) + 1, 2, '0', STR_PAD_LEFT) : '01';
        $previewRkhNo = "RKH{$day}{$month}{$newNumber}{$year}";

        $herbisidadosages = new Herbisidadosage;
        $absentenagakerjamodel = new AbsenTenagaKerja;

        $mandors = User::getMandorByCompany($companycode);
        $activities = Activity::with(['group', 'jenistenagakerja'])->orderBy('activitycode')->get();
        $bloks = Blok::orderBy('blok')->get();
        $masterlist = Masterlist::orderBy('companycode')->orderBy('plot')->get();
        $plots = DB::table('plot')->where('companycode', $companycode)->get();
        $absentenagakerja = $absentenagakerjamodel->getDataAbsenFull($companycode, $targetDate);
        $herbisidagroups = $herbisidadosages->getFullHerbisidaGroupData($companycode);

        return view('input.kerjaharian.rencanakerjaharian.create', [
            'title' => 'Form RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhno' => $previewRkhNo,
            'selectedDate' => $targetDate->format('Y-m-d'),
            'mandors' => $mandors,
            'activities' => $activities,
            'bloks' => $bloks,
            'masterlist' => $masterlist,
            'plots' => $plots,
            'herbisidagroups' => $herbisidagroups,
            'bloksData' => $bloks,
            'masterlistData' => $masterlist,
            'plotsData' => $plots,
            'absentenagakerja' => $absentenagakerja,
            'oldInput' => old(),
        ]);
    }

    public function edit($rkhno)
    {
        $companycode = Session::get('companycode');
        
        $rkhHeader = DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select(['r.*', 'm.name as mandor_nama'])
            ->first();
        
        if (!$rkhHeader) {
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'Data RKH tidak ditemukan');
        }
        
        $rkhDetails = DB::table('rkhlst as r')
            ->leftJoin('herbisidagroup as hg', function($join) {
                $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                     ->on('r.activitycode', '=', 'hg.activitycode');
            })
            ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select(['r.*', 'hg.herbisidagroupname', 'a.activityname', 'a.jenistenagakerja'])
            ->get();
        
        $herbisidadosages = new Herbisidadosage;
        $absentenagakerjamodel = new AbsenTenagaKerja;
        
        $mandors = User::getMandorByCompany($companycode);
        $activities = Activity::with(['group', 'jenistenagakerja'])->orderBy('activitycode')->get();
        $bloks = Blok::orderBy('blok')->get();
        $masterlist = Masterlist::orderBy('companycode')->orderBy('plot')->get();
        $plots = DB::table('plot')->where('companycode', $companycode)->get();
        $absentenagakerja = $absentenagakerjamodel->getDataAbsenFull($companycode, Carbon::parse($rkhHeader->rkhdate));
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
            'plots' => $plots,
            'herbisidagroups' => $herbisidagroups,
            'bloksData' => $bloks,
            'masterlistData' => $masterlist,
            'plotsData' => $plots,
            'absentenagakerja' => $absentenagakerja,
            'oldInput' => old(),
        ]);
    }

    public function update(Request $request, $rkhno)
    {
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
                'rows.*.luas'            => 'required|numeric|min:0',
                'rows.*.laki_laki'       => 'required|integer|min:0',
                'rows.*.perempuan'       => 'required|integer|min:0',
                'rows.*.usingvehicle'    => 'required|boolean',
                'rows.*.material_group_id' => 'nullable|integer',
                'rows.*.keterangan'      => 'nullable|string|max:300',
            ]);

            DB::transaction(function () use ($request, $rkhno) {
                $companycode = Session::get('companycode');
                $tanggal = Carbon::parse($request->input('tanggal'))->format('Y-m-d');

                $totalLuas = collect($request->rows)->sum('luas');
                $totalManpower = collect($request->rows)->sum(function ($row) {
                    return ((int) ($row['laki_laki'] ?? 0)) + ((int) ($row['perempuan'] ?? 0));
                });

                $primaryActivityGroup = null;
                foreach ($request->rows as $row) {
                    if (!empty($row['nama'])) {
                        $activity = Activity::where('activitycode', $row['nama'])->first();
                        if ($activity && $activity->activitygroup) {
                            $primaryActivityGroup = $activity->activitygroup;
                            break;
                        }
                    }
                }

                $approvalData = [];
                if ($primaryActivityGroup) {
                    $approvalSetting = DB::table('approval')
                        ->where('companycode', $companycode)
                        ->where('activitygroup', $primaryActivityGroup)
                        ->first();
                    
                    if ($approvalSetting) {
                        $approvalData = [
                            'activitygroup' => $primaryActivityGroup,
                            'jumlahapproval' => $approvalSetting->jumlahapproval,
                            'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
                            'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
                            'approval3idjabatan' => $approvalSetting->idjabatanapproval3,
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

                $updateData = array_merge([
                    'rkhdate'     => $tanggal,
                    'totalluas'   => $totalLuas,
                    'manpower'    => $totalManpower,
                    'mandorid'    => $request->input('mandor_id'),
                    'updateby'    => Auth::user()->userid,
                    'updatedat'   => now(),
                ], $approvalData);

                DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->update($updateData);
                DB::table('rkhlst')->where('companycode', $companycode)->where('rkhno', $rkhno)->delete();

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
                        'herbisidagroupid'    => !empty($row['material_group_id']) ? (int) $row['material_group_id'] : null,
                        'usingvehicle'        => $row['usingvehicle'],
                        'description'         => $row['keterangan'] ?? null,
                    ];
                }

                DB::table('rkhlst')->insert($details);
            });

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
                
        } catch (\Exception $e) {
            \Log::error("Update RKH Error: " . $e->getMessage());

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

    public function show($rkhno)
    {
        $companycode = Session::get('companycode');
        
        $rkhHeader = DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
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
            ])
            ->first();
        
        if (!$rkhHeader) {
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'Data RKH tidak ditemukan');
        }
        
        $rkhDetails = DB::table('rkhlst as r')
            ->leftJoin('herbisidagroup as hg', function($join) {
                $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                    ->on('r.activitycode', '=', 'hg.activitycode');
            })
            ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select(['r.*', 'hg.herbisidagroupname', 'a.activityname', 'a.jenistenagakerja'])
            ->get();
        
        $absentenagakerjamodel = new AbsenTenagaKerja;
        $absentenagakerja = $absentenagakerjamodel->getDataAbsenFull($companycode, Carbon::parse($rkhHeader->rkhdate));
        
        return view('input.kerjaharian.rencanakerjaharian.show', [
            'title' => 'Detail RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhHeader' => $rkhHeader,
            'rkhDetails' => $rkhDetails,
            'absentenagakerja' => $absentenagakerja,
        ]);
    }

    public function destroy($rkhno)
    {
        $companycode = Session::get('companycode');
        
        try {
            DB::beginTransaction();
            
            DB::table('rkhlst')->where('companycode', $companycode)->where('rkhno', $rkhno)->delete();
            $deleted = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->delete();
            
            if ($deleted) {
                DB::commit();
                return response()->json(['success' => true, 'message' => 'RKH berhasil dihapus']);
            } else {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan'], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus RKH: ' . $e->getMessage()], 500);
        }
    }

    // =========================
    // LKH METHODS
    // =========================
    public function getLKHData($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            \Log::info("Getting LKH data for RKH: {$rkhno}, Company: {$companycode}");
            
            $lkhList = DB::table('lkhhdr as h')
                ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
                ->leftJoin('approval as app', function($join) use ($companycode) {
                    $join->on('a.activitygroup', '=', 'app.activitygroup')
                         ->where('app.companycode', '=', $companycode);
                })
                ->where('h.companycode', $companycode)
                ->where('h.rkhno', $rkhno)
                ->select([
                    'h.lkhno',
                    'h.activitycode',
                    'a.activityname',
                    'h.blok',
                    'h.jenistenagakerja',
                    'h.status',
                    'h.lkhdate',
                    'h.totalworkers',
                    'h.totalhasil',
                    'h.totalsisa',
                    'h.createdat',
                    'h.issubmit',  // Changed from islocked
                    'h.submitby', // Changed from lockedby
                    'h.submitat', // Changed from lockedat
                    'h.jumlahapproval',
                    'h.approval1flag',
                    'h.approval2flag',
                    'h.approval3flag',
                    'app.jumlahapproval as required_approvals'
                ])
                ->orderBy('h.lkhno')
                ->get();

            \Log::info("Found {$lkhList->count()} LKH records for RKH {$rkhno}");

            $formattedData = $lkhList->map(function($lkh) {
                $approvalStatus = $this->calculateLKHApprovalStatus($lkh);
                $canEdit = !$lkh->issubmit && !$this->isLKHFullyApproved($lkh);
                $canSubmit = !$lkh->issubmit && in_array($lkh->status, ['COMPLETED', 'DRAFT']) && !$this->isLKHFullyApproved($lkh);

                return [
                    'lkhno' => $lkh->lkhno,
                    'activity' => $lkh->activitycode . ' - ' . ($lkh->activityname ?? 'Unknown Activity'),
                    'blok' => $lkh->blok ?? 'N/A',
                    'jenis_tenaga' => $lkh->jenistenagakerja == 1 ? 'Harian' : 'Borongan',
                    'status' => $lkh->status ?? 'EMPTY',
                    'approval_status' => $approvalStatus,
                    'issubmit' => (bool) $lkh->issubmit,
                    'date_formatted' => $lkh->lkhdate ? Carbon::parse($lkh->lkhdate)->format('d/m/Y') : '-',
                    'created_at' => $lkh->createdat ? Carbon::parse($lkh->createdat)->format('d/m/Y H:i') : '-',
                    'submit_info' => $lkh->submitat ? 'Submitted at ' . Carbon::parse($lkh->submitat)->format('d/m/Y H:i') : null,
                    'can_edit' => $canEdit,
                    'can_submit' => $canSubmit,
                    'view_url' => route('input.kerjaharian.rencanakerjaharian.showLKH', $lkh->lkhno),
                    'edit_url' => route('input.kerjaharian.rencanakerjaharian.editLKH', $lkh->lkhno)
                ];
            });

            $canGenerateLkh = false;
            $generateMessage = '';
            
            $rkhData = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->first();
                
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

            \Log::info("Returning LKH data", [
                'success' => true,
                'total_lkh' => $lkhList->count(),
                'formatted_count' => $formattedData->count()
            ]);

            return response()->json([
                'success' => true,
                'lkh_data' => $formattedData->values()->toArray(),
                'rkhno' => $rkhno,
                'can_generate_lkh' => $canGenerateLkh,
                'generate_message' => $generateMessage,
                'total_lkh' => $lkhList->count()
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting LKH data for RKH {$rkhno}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data LKH: ' . $e->getMessage(),
                'lkh_data' => [],
                'total_lkh' => 0
            ], 500);
        }
    }

    // =========================
    // LKH APPROVAL METHODS
    // =========================
    public function getPendingLKHApprovals(Request $request)
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

            $pendingLKH = DB::table('lkhhdr as h')
                ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
                ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
                ->where('h.companycode', $companycode)
                ->where('h.issubmit', 1) // Changed from islocked
                ->where(function($query) use ($currentUser) {
                    $query->where(function($q) use ($currentUser) {
                        $q->where('h.approval1idjabatan', $currentUser->idjabatan)->whereNull('h.approval1flag');
                    })->orWhere(function($q) use ($currentUser) {
                        $q->where('h.approval2idjabatan', $currentUser->idjabatan)->where('h.approval1flag', '1')->whereNull('h.approval2flag');
                    })->orWhere(function($q) use ($currentUser) {
                        $q->where('h.approval3idjabatan', $currentUser->idjabatan)->where('h.approval1flag', '1')->where('h.approval2flag', '1')->whereNull('h.approval3flag');
                    });
                })
                ->select([
                    'h.*',
                    'm.name as mandor_nama',
                    'a.activityname',
                    DB::raw('CASE 
                        WHEN h.approval1idjabatan = '.$currentUser->idjabatan.' AND h.approval1flag IS NULL THEN 1
                        WHEN h.approval2idjabatan = '.$currentUser->idjabatan.' AND h.approval1flag = "1" AND h.approval2flag IS NULL THEN 2
                        WHEN h.approval3idjabatan = '.$currentUser->idjabatan.' AND h.approval1flag = "1" AND h.approval2flag = "1" AND h.approval3flag IS NULL THEN 3
                        ELSE 0
                    END as approval_level')
                ])
                ->orderBy('h.lkhdate', 'desc')
                ->get();

            $formattedData = $pendingLKH->map(function($lkh) {
                return [
                    'lkhno' => $lkh->lkhno,
                    'rkhno' => $lkh->rkhno,
                    'lkhdate' => $lkh->lkhdate,
                    'lkhdate_formatted' => Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
                    'mandor_nama' => $lkh->mandor_nama,
                    'activityname' => $lkh->activityname ?? 'Unknown Activity',
                    'approval_level' => $lkh->approval_level,
                    'status' => $lkh->status,
                    'total_workers' => $lkh->totalworkers,
                    'total_hasil' => $lkh->totalhasil,
                    'blok' => $lkh->blok,
                    'plot' => $lkh->plot
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
            \Log::error("Error getting pending LKH approvals: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data approval LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLkhApprovalDetail($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $lkh = DB::table('lkhhdr as h')
                ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
                ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
                ->leftJoin('approval as app', function($join) use ($companycode) {
                    $join->on('a.activitygroup', '=', 'app.activitygroup')
                         ->where('app.companycode', '=', $companycode);
                })
                ->leftJoin('user as u1', 'h.approval1userid', '=', 'u1.userid')
                ->leftJoin('user as u2', 'h.approval2userid', '=', 'u2.userid')
                ->leftJoin('user as u3', 'h.approval3userid', '=', 'u3.userid')
                ->leftJoin('jabatan as j1', 'h.approval1idjabatan', '=', 'j1.idjabatan')
                ->leftJoin('jabatan as j2', 'h.approval2idjabatan', '=', 'j2.idjabatan')
                ->leftJoin('jabatan as j3', 'h.approval3idjabatan', '=', 'j3.idjabatan')
                ->where('h.companycode', $companycode)
                ->where('h.lkhno', $lkhno)
                ->select([
                    'h.*',
                    'm.name as mandor_nama',
                    'a.activityname',
                    'h.jumlahapproval',
                    'h.approval1idjabatan',
                    'h.approval2idjabatan', 
                    'h.approval3idjabatan',
                    'u1.name as approval1_user_name',
                    'u2.name as approval2_user_name',
                    'u3.name as approval3_user_name',
                    'j1.namajabatan as jabatan1_name',
                    'j2.namajabatan as jabatan2_name',
                    'j3.namajabatan as jabatan3_name'
                ])
                ->first();

            if (!$lkh) {
                return response()->json(['success' => false, 'message' => 'LKH tidak ditemukan']);
            }

            $levels = [];
            
            for ($i = 1; $i <= 3; $i++) {
                $jabatanId = $lkh->{"approval{$i}idjabatan"};
                if (!$jabatanId) continue;

                $flagField = "approval{$i}flag";
                $dateField = "approval{$i}date";
                $userField = "approval{$i}_user_name";
                $jabatanField = "jabatan{$i}_name";

                $flag = $lkh->$flagField;
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
                    'jabatan_name' => $lkh->$jabatanField ?? 'Unknown',
                    'status' => $status,
                    'status_text' => $statusText,
                    'user_name' => $lkh->$userField ?? null,
                    'date_formatted' => $lkh->$dateField ? Carbon::parse($lkh->$dateField)->format('d/m/Y H:i') : null
                ];
            }

            $formattedData = [
                'lkhno' => $lkh->lkhno,
                'rkhno' => $lkh->rkhno,
                'lkhdate' => $lkh->lkhdate,
                'lkhdate_formatted' => Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
                'mandor_nama' => $lkh->mandor_nama,
                'activityname' => $lkh->activityname ?? 'Unknown Activity',
                'blok' => $lkh->blok,
                'jumlah_approval' => $lkh->jumlahapproval ?? 0,
                'levels' => $levels
            ];

            return response()->json(['success' => true, 'data' => $formattedData]);

        } catch (\Exception $e) {
            \Log::error("Error getting LKH approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processLKHApproval(Request $request)
    {
        $request->validate([
            'lkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            $lkhno = $request->lkhno;
            $action = $request->action;
            $level = $request->level;

            if (!$currentUser || !$currentUser->idjabatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki jabatan yang valid'
                ]);
            }

            $lkh = DB::table('lkhhdr')->where('companycode', $companycode)->where('lkhno', $lkhno)->first();

            if (!$lkh) {
                return response()->json(['success' => false, 'message' => 'LKH tidak ditemukan']);
            }

            $canApprove = false;
            $approvalField = '';
            $approvalDateField = '';
            $approvalUserField = '';

            switch ($level) {
                case 1:
                    if ($lkh->approval1idjabatan == $currentUser->idjabatan && is_null($lkh->approval1flag)) {
                        $canApprove = true;
                        $approvalField = 'approval1flag';
                        $approvalDateField = 'approval1date';
                        $approvalUserField = 'approval1userid';
                    }
                    break;
                case 2:
                    if ($lkh->approval2idjabatan == $currentUser->idjabatan && 
                        $lkh->approval1flag == '1' && is_null($lkh->approval2flag)) {
                        $canApprove = true;
                        $approvalField = 'approval2flag';
                        $approvalDateField = 'approval2date';
                        $approvalUserField = 'approval2userid';
                    }
                    break;
                case 3:
                    if ($lkh->approval3idjabatan == $currentUser->idjabatan && 
                        $lkh->approval1flag == '1' && $lkh->approval2flag == '1' && is_null($lkh->approval3flag)) {
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

            $approvalValue = $action === 'approve' ? '1' : '0';
            $updateData = [
                $approvalField => $approvalValue,
                $approvalDateField => now(),
                $approvalUserField => $currentUser->userid,
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ];

            if ($action === 'approve') {
                $tempLkh = clone $lkh;
                $tempLkh->$approvalField = '1';
                
                if ($this->isLKHFullyApproved($tempLkh)) {
                    $updateData['status'] = 'APPROVED';
                }
            }

            DB::table('lkhhdr')->where('companycode', $companycode)->where('lkhno', $lkhno)->update($updateData);

            $responseMessage = 'LKH berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            return response()->json(['success' => true, 'message' => $responseMessage]);

        } catch (\Exception $e) {
            \Log::error("Error processing LKH approval: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses approval LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    // Submit LKH method (changed from lockLKH to submitLKH)
    public function submitLKH(Request $request)
    {
        $request->validate(['lkhno' => 'required|string']);

        try {
            $companycode = Session::get('companycode');
            $lkhno = $request->lkhno;
            $currentUser = Auth::user();

            DB::beginTransaction();

            $lkh = DB::table('lkhhdr as h')
                ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
                ->where('h.companycode', $companycode)
                ->where('h.lkhno', $lkhno)
                ->select(['h.*', 'a.activitygroup'])
                ->first();

            if (!$lkh) {
                return response()->json(['success' => false, 'message' => 'LKH tidak ditemukan']);
            }

            if ($lkh->issubmit) {
                return response()->json(['success' => false, 'message' => 'LKH sudah disubmit sebelumnya']);
            }

            $approvalSetting = null;
            if ($lkh->activitygroup) {
                $approvalSetting = DB::table('approval')
                    ->where('companycode', $companycode)
                    ->where('activitygroup', $lkh->activitygroup)
                    ->first();
            }

            $updateData = [
                'issubmit' => 1,
                'submitby' => $currentUser->userid,
                'submitat' => now(),
                'status' => 'SUBMITTED',
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ];

            if ($approvalSetting) {
                $updateData = array_merge($updateData, [
                    'jumlahapproval' => $approvalSetting->jumlahapproval,
                    'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
                    'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
                    'approval3idjabatan' => $approvalSetting->idjabatanapproval3,
                ]);
            }

            DB::table('lkhhdr')->where('companycode', $companycode)->where('lkhno', $lkhno)->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'LKH berhasil disubmit dan masuk ke proses approval'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error submitting LKH: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // RKH APPROVAL METHODS
    // =========================
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

            $pendingRKH = DB::table('rkhhdr as r')
                ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
                ->leftJoin('approval as app', function($join) use ($companycode) {
                    $join->on('r.activitygroup', '=', 'app.activitygroup')
                         ->where('app.companycode', '=', $companycode);
                })
                ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
                ->where('r.companycode', $companycode)
                ->where(function($query) use ($currentUser) {
                    $query->where(function($q) use ($currentUser) {
                        $q->where('app.idjabatanapproval1', $currentUser->idjabatan)->whereNull('r.approval1flag');
                    })->orWhere(function($q) use ($currentUser) {
                        $q->where('app.idjabatanapproval2', $currentUser->idjabatan)->where('r.approval1flag', '1')->whereNull('r.approval2flag');
                    })->orWhere(function($q) use ($currentUser) {
                        $q->where('app.idjabatanapproval3', $currentUser->idjabatan)->where('r.approval1flag', '1')->where('r.approval2flag', '1')->whereNull('r.approval3flag');
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
                    DB::raw('CASE 
                        WHEN app.idjabatanapproval1 = '.$currentUser->idjabatan.' AND r.approval1flag IS NULL THEN 1
                        WHEN app.idjabatanapproval2 = '.$currentUser->idjabatan.' AND r.approval1flag = "1" AND r.approval2flag IS NULL THEN 2
                        WHEN app.idjabatanapproval3 = '.$currentUser->idjabatan.' AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag IS NULL THEN 3
                        ELSE 0
                    END as approval_level')
                ])
                ->orderBy('r.rkhdate', 'desc')
                ->get();

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

            $approvalValue = $action === 'approve' ? '1' : '0';
            
            DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->update([
                $approvalField => $approvalValue,
                $approvalDateField => now(),
                $approvalUserField => $currentUser->userid,
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ]);

            $responseMessage = 'RKH berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            if ($action === 'approve') {
                $updatedRkh = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->first();

                if ($this->isRkhFullyApproved($updatedRkh)) {
                    try {
                        $lkhGenerator = new LkhGeneratorService();
                        $lkhResult = $lkhGenerator->generateLkhFromRkh($rkhno);
                        
                        if ($lkhResult['success']) {
                            $responseMessage .= '. LKH telah di-generate otomatis (' . $lkhResult['total_lkh'] . ' LKH)';
                        } else {
                            $responseMessage .= '. WARNING: Gagal auto-generate LKH - ' . $lkhResult['message'];
                        }
                    } catch (\Exception $e) {
                        \Log::error("Exception during LKH auto-generation for RKH {$rkhno}: " . $e->getMessage());
                        $responseMessage .= '. WARNING: Error saat auto-generate LKH';
                    }
                }
            }

            return response()->json(['success' => true, 'message' => $responseMessage]);

        } catch (\Exception $e) {
            \Log::error("Error processing approval: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses approval: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getApprovalDetail($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
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
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan']);
            }

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

            return response()->json(['success' => true, 'data' => $formattedData]);

        } catch (\Exception $e) {
            \Log::error("Error getting approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // HELPER METHODS
    // =========================
    private function calculateLKHApprovalStatus($lkh)
    {
        // If not submitted yet, return "Not Yet Submitted"
        if (!$lkh->issubmit) {
            return 'Not Yet Submitted';
        }

        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return 'No Approval Required';
        }

        if ($this->isLKHFullyApproved($lkh)) {
            return 'Approved';
        }

        if ($lkh->approval1flag === '0' || $lkh->approval2flag === '0' || $lkh->approval3flag === '0') {
            return 'Declined';
        }

        $completed = 0;
        if ($lkh->approval1flag === '1') $completed++;
        if ($lkh->approval2flag === '1') $completed++;
        if ($lkh->approval3flag === '1') $completed++;

        return "Waiting ({$completed} / {$lkh->jumlahapproval})";
    }

    private function isLKHFullyApproved($lkh)
    {
        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return true;
        }

        switch ($lkh->jumlahapproval) {
            case 1:
                return $lkh->approval1flag === '1';
            case 2:
                return $lkh->approval1flag === '1' && $lkh->approval2flag === '1';
            case 3:
                return $lkh->approval1flag === '1' && 
                       $lkh->approval2flag === '1' && 
                       $lkh->approval3flag === '1';
            default:
                return false;
        }
    }

    private function isRkhFullyApproved($rkh)
    {
        if (!$rkh->jumlahapproval || $rkh->jumlahapproval == 0) {
            return true;
        }

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

    private function getJabatanName($idjabatan)
    {
        $jabatan = DB::table('jabatan')->where('idjabatan', $idjabatan)->first();
        return $jabatan ? $jabatan->namajabatan : 'Unknown';
    }

    // ... (other existing methods like showLKH, editLKH, DTH methods, etc. remain the same) ...

    public function showLKH($lkhno)
{
    try {
        $companycode = Session::get('companycode');
        
        $lkhData = DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
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

        // Update query untuk menggunakan JOIN dengan tenagakerja
        $lkhDetails = DB::table('lkhlst as l')
            ->leftJoin('tenagakerja as t', 'l.idtenagakerja', '=', 't.tenagakerjaid')
            ->where('l.lkhno', $lkhno)
            ->select([
                'l.*',
                't.nama as workername',
                't.nik as noktp'
            ])
            ->orderBy('l.tenagakerjaurutan')
            ->get();

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
            $approvals->jabatan4name = null;
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



// EDIT LKH START

// Add these methods to your RencanaKerjaHarianController

public function editLKH($lkhno)
{
    try {
        $companycode = Session::get('companycode');
        
        $lkhData = DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandornama',
                'a.activityname'
            ])
            ->first();

        if (!$lkhData) {
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'Data LKH tidak ditemukan');
        }

        // Check if LKH can be edited (not locked/submitted)
        if ($lkhData->issubmit) {
            return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
                ->with('error', 'LKH sudah disubmit dan tidak dapat diedit');
        }

        $lkhDetails = DB::table('lkhlst as l')
            ->leftJoin('tenagakerja as t', 'l.idtenagakerja', '=', 't.tenagakerjaid')
            ->where('l.lkhno', $lkhno)
            ->select([
                'l.*',
                't.nama as workername',
                't.nik as noktp'
            ])
            ->orderBy('l.tenagakerjaurutan')
            ->get();

        // Load data for modals
        $tenagaKerja = DB::table('tenagakerja')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->select(['tenagakerjaid', 'nama', 'nik', 'jenistenagakerja'])
            ->orderBy('nama')
            ->get();

        $bloks = Blok::orderBy('blok')->get();
        $masterlist = Masterlist::orderBy('companycode')->orderBy('plot')->get();
        $plots = DB::table('plot')->where('companycode', $companycode)->get();

        return view('input.kerjaharian.rencanakerjaharian.edit-lkh', [
            'title' => 'Edit LKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'lkhData' => $lkhData,
            'lkhDetails' => $lkhDetails,
            'tenagaKerja' => $tenagaKerja,
            'bloks' => $bloks,
            'masterlist' => $masterlist,
            'plots' => $plots,
            'bloksData' => $bloks,
            'masterlistData' => $masterlist,
            'plotsData' => $plots
        ]);

    } catch (\Exception $e) {
        \Log::error("Error editing LKH: " . $e->getMessage());
        return redirect()->route('input.kerjaharian.rencanakerjaharian.index')
            ->with('error', 'Terjadi kesalahan saat membuka edit LKH: ' . $e->getMessage());
    }
}

public function updateLKH(Request $request, $lkhno)
{
    try {
        $request->validate([
            'keterangan' => 'nullable|string|max:500',
            'workers' => 'required|array|min:1',
            'workers.*.tenagakerjaid' => 'required|string',
            'workers.*.blok' => 'required|string',
            'workers.*.plot' => 'required|string',
            'workers.*.luasplot' => 'required|numeric|min:0',
            'workers.*.hasil' => 'required|numeric|min:0',
            'workers.*.sisa' => 'required|numeric|min:0',
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        
        DB::beginTransaction();

        // Check if LKH exists and can be edited
        $lkhData = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->first();

        if (!$lkhData) {
            throw new \Exception('LKH tidak ditemukan');
        }

        if ($lkhData->issubmit) {
            throw new \Exception('LKH sudah disubmit dan tidak dapat diedit');
        }

        // Calculate totals
        $totalWorkers = count($request->workers);
        $totalHasil = collect($request->workers)->sum('hasil');
        $totalSisa = collect($request->workers)->sum('sisa');
        $totalUpah = 0;

        // Calculate total upah based on jenistenagakerja
        foreach ($request->workers as $worker) {
            if ($lkhData->jenistenagakerja == 1) {
                // Harian: upah harian + premi + overtime
                $upahHarian = $worker['upahharian'] ?? 0;
                $premi = $worker['premi'] ?? 0;
                $overtimeHours = $worker['overtimehours'] ?? 0;
                // You might need to calculate overtime rate
                $totalUpah += $upahHarian + $premi;
            } else {
                // Borongan: hasil * cost per ha
                $hasil = $worker['hasil'] ?? 0;
                $costPerHa = $worker['costperha'] ?? 0;
                $totalUpah += $hasil * $costPerHa;
            }
        }

        // Update LKH Header
        DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->update([
                'totalworkers' => $totalWorkers,
                'totalhasil' => $totalHasil,
                'totalsisa' => $totalSisa,
                'totalupahall' => $totalUpah,
                'keterangan' => $request->keterangan,
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ]);

        // Delete existing details
        DB::table('lkhlst')->where('lkhno', $lkhno)->delete();

        // Insert new details
        $details = [];
        foreach ($request->workers as $index => $worker) {
            $detail = [
                'lkhno' => $lkhno,
                'tenagakerjaurutan' => $index + 1,
                'tenagakerjaid' => $worker['tenagakerjaid'],
                'blok' => $worker['blok'],
                'plot' => $worker['plot'],
                'luasplot' => $worker['luasplot'],
                'hasil' => $worker['hasil'],
                'sisa' => $worker['sisa'],
                'materialused' => $worker['materialused'] ?? null,
                'createdat' => now()
            ];

            if ($lkhData->jenistenagakerja == 1) {
                // Tenaga Harian fields
                $detail['jammasuk'] = $worker['jammasuk'] ?? null;
                $detail['jamselesai'] = $worker['jamselesai'] ?? null;
                $detail['overtimehours'] = $worker['overtimehours'] ?? 0;
                $detail['premi'] = $worker['premi'] ?? 0;
                $detail['upahharian'] = $worker['upahharian'] ?? 0;
                $detail['totalupahharian'] = ($worker['upahharian'] ?? 0) + ($worker['premi'] ?? 0);
                $detail['costperha'] = 0;
                $detail['totalbiayaborongan'] = 0;
            } else {
                // Tenaga Borongan fields
                $detail['jammasuk'] = null;
                $detail['jamselesai'] = null;
                $detail['overtimehours'] = 0;
                $detail['premi'] = 0;
                $detail['upahharian'] = 0;
                $detail['totalupahharian'] = 0;
                $detail['costperha'] = $worker['costperha'] ?? 0;
                $detail['totalbiayaborongan'] = ($worker['hasil'] ?? 0) * ($worker['costperha'] ?? 0);
            }

            $details[] = $detail;
        }

        DB::table('lkhlst')->insert($details);

        DB::commit();

        return redirect()->route('input.kerjaharian.rencanakerjaharian.showLKH', $lkhno)
            ->with('success', 'LKH berhasil diupdate');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error updating LKH: " . $e->getMessage());
        
        return redirect()->back()
            ->withInput()
            ->with('error', 'Terjadi kesalahan saat mengupdate LKH: ' . $e->getMessage());
    }
}
// EDIT LKH END


















    // DTH Methods
    public function getDTHData(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $companycode = Session::get('companycode');
        
        try {
            $query = DB::table('rkhhdr as h')
                ->join('rkhlst as l', 'h.rkhno', '=', 'l.rkhno')
                ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
                ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
                ->where('h.companycode', $companycode)
                ->whereDate('h.rkhdate', $date)
                ->where('h.approval1flag', '1')
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
            $harianData = $allData->where('jenistenagakerja', 1)->values()->toArray();
            $boronganData = $allData->where('jenistenagakerja', 2)->values()->toArray();

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
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data DTH: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showDTHReport(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        return view('input.kerjaharian.rencanakerjaharian.dth-report', ['date' => $date]);
    }

    public function generateDTH(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $url = route('input.kerjaharian.rencanakerjaharian.dth-report', ['date' => $request->date]);
        
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

    public function manualGenerateLkh(Request $request, $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $rkh = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->first();
                
            if (!$rkh) {
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan']);
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
                return response()->json(['success' => true, 'message' => 'Status RKH berhasil diupdate']);
            } else {
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan'], 404);
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