<?php

namespace App\Http\Controllers\Input;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\Notification;

class HPTController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Input Data',
            'nav' => 'HPT',
            'routeName' => route('input.hpt.index'),
        ]);
    }
    public function index(Request $request)
    {
        $title = "Daftar HPT";
        $search = $request->input('search', '');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $userid = Auth::user()->userid;
        $companycode = DB::table('usercompany')
            ->where('userid', $userid)
            ->value('companycode');
        $companyArray = $companycode ? explode(',', $companycode) : [];

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $hpt = DB::table('hpthdr')
            ->join('company', 'hpthdr.companycode', '=', 'company.companycode')
            ->where('hpthdr.companycode', '=', session('companycode'))
            ->where('hpthdr.closingperiode', '=', 'F')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('hpthdr.createdat', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('hpthdr.createdat', '<=', $endDate);
            });

        if (!empty($search)) {
            $hpt->where(function ($query) use ($search) {
                $query->where('hpthdr.idblokplot', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.nosample', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.varietas', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.plot', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.kat', 'like', '%' . $search . '%');
            });

        }
        $hpt = $hpt->select(
            'hpthdr.*',
            'company.nama as nama_comp'
        )
            ->orderBy('hpthdr.createdat', 'desc')
            ->paginate($perPage);

        foreach ($hpt as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
        }

        foreach ($hpt as $index => $item) {
            $item->no = ($hpt->currentPage() - 1) * $hpt->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('input.hpt.index', compact('hpt', 'perPage', 'startDate', 'endDate', 'title', 'search'));
        }
        return view('input.hpt.index', compact('hpt', 'perPage', 'startDate', 'endDate', 'title', 'search'));
    }

    public function handle(Request $request)
    {
        if ($request->has('filter') || $request->has('perPage')) {
            return $this->index($request);
        }

        return $this->store($request);
    }

    public function create()
    {
        $title = "Create Data";
        $mapping = DB::table('mapping')->where('companycode', '=', session('companycode'))->get();
        $method = 'POST';
        $url = route('input.hpt.handle');
        $buttonSubmit = 'Create';
        return view('input.hpt.form', compact('mapping', 'title', 'method', 'url', 'buttonSubmit'));
    }

    public function getFieldByMapping(Request $request)
    {
        $idblokplot = $request->input('idblokplot');
        $mapping = DB::table('mapping')
            ->where('companycode', session('companycode'))
            ->where('idblokplot', $idblokplot)->first();

        if ($mapping) {
            return response()->json([
                'companycode' => $mapping->companycode,
                'blok' => $mapping->blok,
                'plot' => $mapping->plot,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function checkData(Request $request)
    {
        $noSample = $request->get('nosample');
        $kdPlotSample = $request->get('idblokplot');

        $data = DB::table('hpthdr')
            ->where('nosample', $noSample)
            ->where('idblokplot', $kdPlotSample)
            ->where('companycode', session('companycode'))
            ->first();

        if ($data) {
            return response()->json([
                'success' => true,
                'kat' => $data->kat,
                'varietas' => $data->varietas,
                'tanggaltanam' => $data->tanggaltanam,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data not found',
        ]);
    }

    protected function requestValidated(): array
    {
        return [
            'nosample' => 'required',
            'companycode' => 'required',
            'blok' => 'required',
            'plot' => 'required',
            'idblokplot' => 'required|exists:mapping,idblokplot',
            'varietas' => 'required',
            'kat' => 'required',
            'tanggaltanam' => 'required',
            'tanggalpengamatan' => 'required',
            'lists.*.nourut' => 'required',
            'lists.*.ppt_aktif' => 'required',
            'lists.*.pbt_aktif' => 'required',
            'lists.*.skor0' => 'required',
            'lists.*.skor1' => 'required',
            'lists.*.skor2' => 'required',
            'lists.*.skor3' => 'required',
            'lists.*.skor4' => 'required',
            'lists.*.telur_ppt' => 'required',
            'lists.*.larva_ppt1' => 'required',
            'lists.*.larva_ppt2' => 'required',
            'lists.*.larva_ppt3' => 'required',
            'lists.*.larva_ppt4' => 'required',
            'lists.*.pupa_ppt' => 'required',
            'lists.*.ngengat_ppt' => 'required',
            'lists.*.kosong_ppt' => 'required',
            'lists.*.telur_pbt' => 'required',
            'lists.*.larva_pbt1' => 'required',
            'lists.*.larva_pbt2' => 'required',
            'lists.*.larva_pbt3' => 'required',
            'lists.*.larva_pbt4' => 'required',
            'lists.*.pupa_pbt' => 'required',
            'lists.*.ngengat_pbt' => 'required',
            'lists.*.kosong_pbt' => 'required',
            'lists.*.dh' => 'required',
            'lists.*.dt' => 'required',
            'lists.*.kbp' => 'required',
            'lists.*.kbb' => 'required',
            'lists.*.kp' => 'required',
            'lists.*.cabuk' => 'required',
            'lists.*.belalang' => 'required',
            'lists.*.serang_grayak' => 'required',
            'lists.*.jum_grayak' => 'required',
            'lists.*.serang_smut' => 'required',
            'lists.*.smut_stadia1' => 'required',
            'lists.*.smut_stadia2' => 'required',
            'lists.*.smut_stadia3' => 'required',
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());
        $notifController = new NotificationController();

        $existsInHeader = DB::table('hpthdr')->where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggalpengamatan', $request->tanggalpengamatan)
            ->exists();

        $existsInLists = DB::table('hptlst')->where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggalpengamatan', $request->tanggalpengamatan)
            ->exists();

        if ($existsInHeader || $existsInLists) {
            return back()->with([
                'success1' => 'Data sudah ada di salah satu tabel, silahkan coba dengan data yang berbeda.',
            ])->withInput();
        }

        DB::beginTransaction();

        try {

            DB::table('hpthdr')->insert([
                'nosample' => $validated['nosample'],
                'companycode' => $validated['companycode'],
                'blok' => $validated['blok'],
                'plot' => $validated['plot'],
                'idblokplot' => $validated['idblokplot'],
                'varietas' => $validated['varietas'],
                'kat' => $validated['kat'],
                'tanggaltanam' => $validated['tanggaltanam'],
                'tanggalpengamatan' => $validated['tanggalpengamatan'],
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updatedat' => now(),
            ]);

            $totalPerPPT = 0;
            $totalPerPBT = 0;
            $count = count($validated['lists']);

            foreach ($validated['lists'] as $list) {
                $jumlahbatang = $list['skor0'] + $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $ppt = ($list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'] + $list['pupa_ppt']) + $list['kosong_ppt'];
                $pbt = $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $sum_ni = $list['skor0'] * 0 + $list['skor1'] * 1 + $list['skor2'] * 2 + $list['skor3'] * 3 + $list['skor4'] * 4;
                $per_ppt = $jumlahbatang != 0 ? $ppt / $jumlahbatang : 0;
                $per_pbt = $jumlahbatang != 0 ? $pbt / $jumlahbatang : 0;
                $umur_tanam = round($validated['tanggaltanam'] ? Carbon::parse($validated['tanggaltanam'])->diffInMonths(Carbon::now()) : null);

                DB::table('hptlst')->insert([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $jumlahbatang,
                    'ppt' => $ppt,
                    'ppt_aktif' => $list['ppt_aktif'],
                    'pbt' => $pbt,
                    'pbt_aktif' => $list['pbt_aktif'],
                    'skor0' => $list['skor0'],
                    'skor1' => $list['skor1'],
                    'skor2' => $list['skor2'],
                    'skor3' => $list['skor3'],
                    'skor4' => $list['skor4'],
                    'per_ppt' => $per_ppt,
                    'per_ppt_aktif' => $jumlahbatang != 0 ? $list['ppt_aktif'] / $jumlahbatang : 0,
                    'per_pbt' => $per_pbt,
                    'per_pbt_aktif' => $jumlahbatang != 0 ? $list['pbt_aktif'] / $jumlahbatang : 0,
                    'sum_ni' => $sum_ni,
                    'int_rusak' => $jumlahbatang != 0 ? $sum_ni / ($jumlahbatang * 4) : 0,
                    'telur_ppt' => $list['telur_ppt'],
                    'larva_ppt1' => $list['larva_ppt1'],
                    'larva_ppt2' => $list['larva_ppt2'],
                    'larva_ppt3' => $list['larva_ppt3'],
                    'larva_ppt4' => $list['larva_ppt4'],
                    'pupa_ppt' => $list['pupa_ppt'],
                    'ngengat_ppt' => $list['ngengat_ppt'],
                    'kosong_ppt' => $list['kosong_ppt'],
                    'telur_pbt' => $list['telur_pbt'],
                    'larva_pbt1' => $list['larva_pbt1'],
                    'larva_pbt2' => $list['larva_pbt2'],
                    'larva_pbt3' => $list['larva_pbt3'],
                    'larva_pbt4' => $list['larva_pbt4'],
                    'pupa_pbt' => $list['pupa_pbt'],
                    'ngengat_pbt' => $list['ngengat_pbt'],
                    'kosong_pbt' => $list['kosong_pbt'],
                    'dh' => $list['dh'],
                    'dt' => $list['dt'],
                    'kbp' => $list['kbp'],
                    'kbb' => $list['kbb'],
                    'kp' => $list['kp'],
                    'cabuk' => $list['cabuk'],
                    'belalang' => $list['belalang'],
                    'serang_grayak' => $list['serang_grayak'],
                    'jum_grayak' => $list['jum_grayak'],
                    'serang_smut' => $list['serang_smut'],
                    'smut_stadia1' => $list['smut_stadia1'],
                    'smut_stadia2' => $list['smut_stadia2'],
                    'smut_stadia3' => $list['smut_stadia3'],
                    'jum_larva_ppt' => $list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'],
                    'jum_larva_pbt' => $list['larva_pbt1'] + $list['larva_pbt2'] + $list['larva_pbt3'] + $list['larva_pbt4'],
                    'inputby' => Auth::user()->userid,
                    'createdat' => now(),
                    'updatedat' => now()
                ]);
                $totalPerPPT += $per_ppt;
                $totalPerPBT += $per_pbt;
            }

            $avgPPT = $count > 0 ? $totalPerPPT / $count : 0;
            $avgPBT = $count > 0 ? $totalPerPBT / $count : 0;
            $umurTanam = $validated['tanggaltanam'] ? Carbon::parse($validated['tanggaltanam'])->diffInMonths(Carbon::now()) : null;

            if (
                ($avgPBT > 0.03 && $umurTanam >= 1 && $umurTanam <= 3) ||
                ($avgPPT > 0.03 && $umurTanam >= 1 && $umurTanam <= 3) ||
                ($avgPBT > 0.05 && $umurTanam >= 4) ||
                ($avgPPT > 0.05 && $umurTanam >= 4)
            ) {
                Notification::createForHPT([
                    'plot' => $validated['plot'],
                    'companycode' => $validated['companycode'],
                    'condition' => [
                        'ppt' => $avgPPT,
                        'pbt' => $avgPBT,
                        'umur' => $umurTanam,
                    ]
                ]);
            }

            DB::commit();

            return redirect()->back()
                ->with('success1', 'Data created successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->route('input.hpt.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }


    public function show($nosample, $companycode, $tanggalpengamatan)
    {
        $hpt = DB::table('hpthdr')
            ->where('companycode', '=', session('companycode'))
            ->where('nosample', $nosample)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->first();

        if (!$hpt) {
            abort(404, 'HPT header not found');
        }

        $hptLists = DB::table('hptlst')
            ->leftJoin('hpthdr', function ($join) use ($hpt) {
                $join->on('hptlst.nosample', '=', 'hpthdr.nosample')
                    ->whereColumn('hptlst.companycode', '=', 'hpthdr.companycode')
                    ->whereColumn('hptlst.tanggalpengamatan', '=', 'hpthdr.tanggalpengamatan');
            })
            ->leftJoin('company', function ($join) {
                $join->on('hpthdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('hpthdr.blok', '=', 'blok.blok')
                    ->whereColumn('hpthdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('hpthdr.plot', '=', 'plot.plot')
                    ->whereColumn('hpthdr.companycode', '=', 'plot.companycode');
            })
            ->select(
                'hptlst.*',
                'hpthdr.varietas',
                'hpthdr.kat',
                'hpthdr.tanggaltanam',
                'company.name as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
            )
            ->where('hptlst.nosample', $nosample)
            ->where('hptlst.companycode', $companycode)
            ->where('hptlst.tanggalpengamatan', $tanggalpengamatan)
            ->orderBy('hptlst.nourut', 'asc')
            ->get();

        $now = Carbon::now();

        $hptLists = $hptLists->map(function ($item) use ($now) {
            $tgl_tanam = Carbon::parse($item->tanggaltanam);
            $item->umur_tanam = $tgl_tanam->diffInMonths($now);
            return $item;
        });

        foreach ($hptLists as $index => $item) {
            $item->no = $index + 1;
        }

        return response()->json($hptLists);
    }

    public function edit($nosample, $companycode, $tanggalpengamatan)
    {
        $title = 'Edit Data';
        $header = DB::table('hpthdr')
            ->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->firstOrFail();
        $lists = DB::table('hptlst')->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->get();

        $list = $lists->first();
        $header->lists = $lists;

        $company = DB::table('company')->get();
        $mapping = DB::table('mapping')->get();
        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('input.hpt.update', ['nosample' => $nosample, 'companycode' => $companycode, 'tanggalpengamatan' => $tanggalpengamatan]);

        if ($header->status === "Posted") {
            return redirect()->route('input.hpt.index')->with('success1', 'Data telah di posting, tidak dapat mengakses edit.');
        }

        return view('input.hpt.form', compact('buttonSubmit', 'header', 'list', 'company', 'mapping', 'title', 'method', 'url'));
    }

    public function update(Request $request, $nosample, $companycode, $tanggalpengamatan)
    {
        $validated = $request->validate($this->requestValidated());

        DB::beginTransaction();

        try {
            DB::table('hpthdr')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->update([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'blok' => $validated['blok'],
                    'plot' => $validated['plot'],
                    'idblokplot' => $validated['idblokplot'],
                    'varietas' => $validated['varietas'],
                    'kat' => $validated['kat'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'updatedat' => now(),
                ]);

            $lists = DB::table('hptlst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan);

            $saved = DB::table('hptlst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->first();

            $createdAt = $saved->createdat;
            $userInput = $saved->inputby;

            $lists->delete();

            foreach ($validated['lists'] as $list) {
                $jumlahbatang = $list['skor0'] + $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $ppt = ($list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'] + $list['pupa_ppt']) + $list['kosong_ppt'];
                $pbt = $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $sum_ni = $list['skor0'] * 0 + $list['skor1'] * 1 + $list['skor2'] * 2 + $list['skor3'] * 3 + $list['skor4'] * 4;
                $data = [
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $jumlahbatang,
                    'ppt' => $ppt,
                    'ppt_aktif' => $list['ppt_aktif'],
                    'pbt' => $pbt,
                    'pbt_aktif' => $list['pbt_aktif'],
                    'skor0' => $list['skor0'],
                    'skor1' => $list['skor1'],
                    'skor2' => $list['skor2'],
                    'skor3' => $list['skor3'],
                    'skor4' => $list['skor4'],
                    'per_ppt' => $jumlahbatang != 0 ? $ppt / $jumlahbatang : 0,
                    'per_ppt_aktif' => $jumlahbatang != 0 ? $list['ppt_aktif'] / $jumlahbatang : 0,
                    'per_pbt' => $jumlahbatang != 0 ? $pbt / $jumlahbatang : 0,
                    'per_pbt_aktif' => $jumlahbatang != 0 ? $list['pbt_aktif'] / $jumlahbatang : 0,
                    'sum_ni' => $sum_ni,
                    'int_rusak' => $jumlahbatang != 0 ? $sum_ni / ($jumlahbatang * 4) : 0,
                    'telur_ppt' => $list['telur_ppt'],
                    'larva_ppt1' => $list['larva_ppt1'],
                    'larva_ppt2' => $list['larva_ppt2'],
                    'larva_ppt3' => $list['larva_ppt3'],
                    'larva_ppt4' => $list['larva_ppt4'],
                    'pupa_ppt' => $list['pupa_ppt'],
                    'ngengat_ppt' => $list['ngengat_ppt'],
                    'kosong_ppt' => $list['kosong_ppt'],
                    'telur_pbt' => $list['telur_pbt'],
                    'larva_pbt1' => $list['larva_pbt1'],
                    'larva_pbt2' => $list['larva_pbt2'],
                    'larva_pbt3' => $list['larva_pbt3'],
                    'larva_pbt4' => $list['larva_pbt4'],
                    'pupa_pbt' => $list['pupa_pbt'],
                    'ngengat_pbt' => $list['ngengat_pbt'],
                    'kosong_pbt' => $list['kosong_pbt'],
                    'dh' => $list['dh'],
                    'dt' => $list['dt'],
                    'kbp' => $list['kbp'],
                    'kbb' => $list['kbb'],
                    'kp' => $list['kp'],
                    'cabuk' => $list['cabuk'],
                    'belalang' => $list['belalang'],
                    'serang_grayak' => $list['serang_grayak'],
                    'jum_grayak' => $list['jum_grayak'],
                    'serang_smut' => $list['serang_smut'],
                    'smut_stadia1' => $list['smut_stadia1'],
                    'smut_stadia2' => $list['smut_stadia2'],
                    'smut_stadia3' => $list['smut_stadia3'],
                    'jum_larva_ppt' => $list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'],
                    'jum_larva_pbt' => $list['larva_pbt1'] + $list['larva_pbt2'] + $list['larva_pbt3'] + $list['larva_pbt4'],
                    'inputby' => $userInput,
                    'createdat' => $createdAt,
                    'updatedat' => now(),
                ];


                DB::table('hptlst')
                    ->where('nosample', $nosample)
                    ->where('companycode', $companycode)
                    ->where('tanggalpengamatan', $tanggalpengamatan)
                    ->where('nourut', $list['nourut'])
                    ->insert($data);
            }

            DB::commit();

            return redirect()->route('input.hpt.index')
                ->with('success1', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('input.hpt.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($nosample, $companycode, $tanggalpengamatan)
    {
        DB::transaction(function () use ($nosample, $companycode, $tanggalpengamatan) {
            DB::table('hpthdr')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->delete();
            DB::table('hptlst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->delete();
        });
        return redirect()->route('input.hpt.index')
            ->with('success', 'Data deleted successfully.');
    }

    public function excel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('hptlst')
            ->leftJoin('hpthdr', function ($join) {
                $join->on('hptlst.nosample', '=', 'hpthdr.nosample')
                    ->whereColumn('hptlst.companycode', '=', 'hpthdr.companycode')
                    ->whereColumn('hptlst.tanggalpengamatan', '=', 'hpthdr.tanggalpengamatan');
            })
            ->leftJoin('company', function ($join) {
                $join->on('hpthdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('hpthdr.blok', '=', 'blok.blok')
                    ->whereColumn('hpthdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('hpthdr.plot', '=', 'plot.plot')
                    ->whereColumn('hpthdr.companycode', '=', 'plot.companycode');
            })
            ->where('hptlst.companycode', session('companycode'))
            ->where('hpthdr.companycode', session('companycode'))
            ->select(
                'hptlst.*',
                'hpthdr.varietas',
                'hpthdr.kat',
                'hpthdr.tanggalpengamatan',
                'company.nama as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
            )
            ->orderBy('hpthdr.tanggalpengamatan', 'desc');


        if ($startDate) {
            $query->whereDate('hpthdr.tanggalpengamatan', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('hpthdr.tanggalpengamatan', '<=', $endDate);
        }
        $hpt = $query->get();

        $now = Carbon::now();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No. Sample');
        $sheet->setCellValue('B1', 'Kebun');
        $sheet->setCellValue('C1', 'Blok');
        $sheet->setCellValue('D1', 'Plot');
        $sheet->setCellValue('E1', 'Luas');
        $sheet->setCellValue('F1', 'Tanggal Tanam');
        $sheet->setCellValue('G1', 'Umur Tanam');
        $sheet->setCellValue('H1', 'Varietas');
        $sheet->setCellValue('I1', 'Kategori');
        $sheet->setCellValue('J1', 'Tanggal Pengamatan');
        $sheet->setCellValue('K1', 'Bulan Pengamatan');
        $sheet->setCellValue('L1', 'No. Urut');
        $sheet->setCellValue('M1', 'Jumlah Batang');
        $sheet->setCellValue('N1', 'PPT');
        $sheet->setCellValue('O1', 'PPT Aktif');
        $sheet->setCellValue('P1', 'PBT');
        $sheet->setCellValue('Q1', 'PBT Aktif');
        $sheet->setCellValue('R1', 'Skor 0');
        $sheet->setCellValue('S1', 'Skor 1');
        $sheet->setCellValue('T1', 'Skor 2');
        $sheet->setCellValue('U1', 'Skor 3');
        $sheet->setCellValue('V1', 'Skor 4');
        $sheet->setCellValue('W1', '%PPT');
        $sheet->setCellValue('X1', '%PPT Aktif');
        $sheet->setCellValue('Y1', '%PBT');
        $sheet->setCellValue('Z1', '%PBT Aktif');
        $sheet->setCellValue('AA1', 'Σni*vi');
        $sheet->setCellValue('AB1', 'Intensitas Kerusakan');
        $sheet->setCellValue('AC1', 'Telur PPT');
        $sheet->setCellValue('AD1', 'Larva PPT 1');
        $sheet->setCellValue('AE1', 'Larva PPT 2');
        $sheet->setCellValue('AF1', 'Larva PPT 3');
        $sheet->setCellValue('AG1', 'Larva PPT 4');
        $sheet->setCellValue('AH1', 'Pupa PPT');
        $sheet->setCellValue('AI1', 'Ngengat PPT');
        $sheet->setCellValue('AJ1', 'Kosong PPT');
        $sheet->setCellValue('AK1', 'Telur PBT');
        $sheet->setCellValue('AL1', 'Larva PBT 1');
        $sheet->setCellValue('AM1', 'Larva PBT 2');
        $sheet->setCellValue('AN1', 'Larva PBT 3');
        $sheet->setCellValue('AO1', 'Larva PBT 4');
        $sheet->setCellValue('AP1', 'Pupa PBT');
        $sheet->setCellValue('AQ1', 'Ngengat PBT');
        $sheet->setCellValue('AR1', 'Kosong PBT');
        $sheet->setCellValue('AS1', 'DH');
        $sheet->setCellValue('AT1', 'DT');
        $sheet->setCellValue('AU1', 'KBP');
        $sheet->setCellValue('AV1', 'KBB');
        $sheet->setCellValue('AW1', 'KP');
        $sheet->setCellValue('AX1', 'Cabuk');
        $sheet->setCellValue('AY1', 'Belalang');
        $sheet->setCellValue('AZ1', 'BTG Terserang Ul.Grayak');
        $sheet->setCellValue('BA1', 'Jumlah Ul.Grayak');
        $sheet->setCellValue('BB1', 'BTG Terserang SMUT');
        $sheet->setCellValue('BC1', 'SMUT Stadia 1');
        $sheet->setCellValue('BD1', 'SMUT Stadia 2');
        $sheet->setCellValue('BE1', 'SMUT Stadia 3');
        $sheet->setCellValue('BF1', 'Jumlah Larva PPT');
        $sheet->setCellValue('BG1', 'Jumlah Larva PBT');

        $sheet->getStyle('A1:BG1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($hpt as $list) {

            $tanggaltanam = Carbon::parse($list->tanggaltanam);
            $umurTanam = $tanggaltanam->diffInMonths($now);

            $tanggalpengamatan = Carbon::parse($list->tanggalpengamatan);
            $bulanPengamatan = $tanggalpengamatan->format('F');

            $sheet->setCellValue('A' . $row, $list->nosample);
            $sheet->setCellValue('B' . $row, $list->compName);
            $sheet->setCellValue('C' . $row, $list->blokName);
            $sheet->setCellValue('D' . $row, $list->plotName);
            $sheet->setCellValue('E' . $row, $list->luasarea);
            $sheet->setCellValue('F' . $row, $tanggaltanam->format('Y-m-d'));
            $sheet->setCellValue('G' . $row, round($umurTanam) . ' Bulan');
            $sheet->setCellValue('H' . $row, $list->varietas);
            $sheet->setCellValue('I' . $row, $list->kat);
            $sheet->setCellValue('J' . $row, $list->tanggalpengamatan);
            $sheet->setCellValue('K' . $row, $bulanPengamatan);
            $sheet->setCellValue('L' . $row, $list->nourut);
            $sheet->setCellValue('M' . $row, $list->jumlahbatang);
            $sheet->setCellValue('N' . $row, $list->ppt);
            $sheet->setCellValue('O' . $row, $list->ppt_aktif);
            $sheet->setCellValue('P' . $row, $list->pbt);
            $sheet->setCellValue('Q' . $row, $list->pbt_aktif);
            $sheet->setCellValue('R' . $row, $list->skor0);
            $sheet->setCellValue('S' . $row, $list->skor1);
            $sheet->setCellValue('T' . $row, $list->skor2);
            $sheet->setCellValue('U' . $row, $list->skor3);
            $sheet->setCellValue('V' . $row, $list->skor4);
            $sheet->setCellValue('W' . $row, $list->per_ppt);
            $sheet->setCellValue('X' . $row, $list->per_ppt_aktif);
            $sheet->setCellValue('Y' . $row, $list->per_pbt);
            $sheet->setCellValue('Z' . $row, $list->per_pbt_aktif);
            $sheet->setCellValue('AA' . $row, $list->sum_ni);
            $sheet->setCellValue('AB' . $row, $list->int_rusak);
            $sheet->setCellValue('AC' . $row, $list->telur_ppt);
            $sheet->setCellValue('AD' . $row, $list->larva_ppt1);
            $sheet->setCellValue('AE' . $row, $list->larva_ppt2);
            $sheet->setCellValue('AF' . $row, $list->larva_ppt3);
            $sheet->setCellValue('AG' . $row, $list->larva_ppt4);
            $sheet->setCellValue('AH' . $row, $list->pupa_ppt);
            $sheet->setCellValue('AI' . $row, $list->ngengat_ppt);
            $sheet->setCellValue('AJ' . $row, $list->kosong_ppt);
            $sheet->setCellValue('AK' . $row, $list->telur_pbt);
            $sheet->setCellValue('AL' . $row, $list->larva_pbt1);
            $sheet->setCellValue('AM' . $row, $list->larva_pbt2);
            $sheet->setCellValue('AN' . $row, $list->larva_pbt3);
            $sheet->setCellValue('AO' . $row, $list->larva_pbt4);
            $sheet->setCellValue('AP' . $row, $list->pupa_pbt);
            $sheet->setCellValue('AQ' . $row, $list->ngengat_pbt);
            $sheet->setCellValue('AR' . $row, $list->kosong_pbt);
            $sheet->setCellValue('AS' . $row, $list->dh);
            $sheet->setCellValue('AT' . $row, $list->dt);
            $sheet->setCellValue('AU' . $row, $list->kbp);
            $sheet->setCellValue('AV' . $row, $list->kbb);
            $sheet->setCellValue('AW' . $row, $list->kp);
            $sheet->setCellValue('AX' . $row, $list->cabuk);
            $sheet->setCellValue('AY' . $row, $list->belalang);
            $sheet->setCellValue('AZ' . $row, $list->serang_grayak);
            $sheet->setCellValue('BA' . $row, $list->jum_grayak);
            $sheet->setCellValue('BB' . $row, $list->serang_smut);
            $sheet->setCellValue('BC' . $row, $list->smut_stadia1);
            $sheet->setCellValue('BD' . $row, $list->smut_stadia2);
            $sheet->setCellValue('BE' . $row, $list->smut_stadia3);
            $sheet->setCellValue('BF' . $row, $list->jum_larva_ppt);
            $sheet->setCellValue('BG' . $row, $list->jum_larva_pbt);

            $sheet->getStyle('W' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('X' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('Y' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('Z' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('AB' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        if ($startDate && $endDate) {
            $filename = "HPTReport_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $filename = "HPTReport.xlsx";
        }
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
