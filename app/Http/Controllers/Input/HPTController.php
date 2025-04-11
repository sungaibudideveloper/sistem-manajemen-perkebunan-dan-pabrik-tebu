<?php

namespace App\Http\Controllers\Input;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use App\Models\Mapping;
use App\Models\HPTHeader;
use App\Models\company;
use Illuminate\Http\Request;
use App\Models\HPTList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $companyArray = explode(',', Auth::user()->userComp->companycode);

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $hpt = HPTHeader::orderBy('createdat', 'desc')->with('lists', 'company')
            ->where('companycode', '=', session('companycode'))
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('createdat', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('createdat', '<=', $endDate);
            })
            ->paginate($perPage);

        foreach ($hpt as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
        }

        foreach ($hpt as $index => $item) {
            $item->no = ($hpt->currentPage() - 1) * $hpt->perPage() + $index + 1;
        }

        return view('input.hpt.index', compact('hpt', 'perPage', 'startDate', 'endDate', 'title'));
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
        $mapping = Mapping::where('companycode', '=', session('companycode'))->get();
        $method = 'POST';
        $url = route('input.hpt.handle');
        $buttonSubmit = 'Create';
        return view('input.hpt.form', compact('mapping', 'title', 'method', 'url', 'buttonSubmit'));
    }

    public function getFieldByMapping(Request $request)
    {
        $idblokplot = $request->input('idblokplot');
        $mapping = Mapping::where('idblokplot', $idblokplot)->first();

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
        $noSample = $request->get('no_sample');
        $kdPlotSample = $request->get('idblokplot');

        $data = DB::table('hpt_hdr')
            ->where('no_sample', $noSample)
            ->where('idblokplot', $kdPlotSample)
            ->first();

        if ($data) {
            return response()->json([
                'success' => true,
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
            'no_sample' => 'required',
            'companycode' => 'required',
            'blok' => 'required',
            'plot' => 'required',
            'idblokplot' => 'required|exists:mapping,idblokplot',
            'varietas' => 'required',
            'tanggaltanam' => 'required',
            'tglamat' => 'required',
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
        // dd($request);
        $validated = $request->validate($this->requestValidated());

        DB::beginTransaction();

        try {

            $header = HPTHeader::create([
                'no_sample' => $validated['no_sample'],
                'companycode' => $validated['companycode'],
                'blok' => $validated['blok'],
                'plot' => $validated['plot'],
                'idblokplot' => $validated['idblokplot'],
                'varietas' => $validated['varietas'],
                'tanggaltanam' => $validated['tanggaltanam'],
                'tglamat' => $validated['tglamat'],
                'inputby' => Auth::user()->usernm,
            ]);

            foreach ($validated['lists'] as $list) {
                $jm_batang = $list['skor0'] + $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $ppt = ($list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'] + $list['pupa_ppt']) + $list['kosong_ppt'];
                $pbt = $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $sum_ni = $list['skor0'] * 0 + $list['skor1'] * 1 + $list['skor2'] * 2 + $list['skor3'] * 3 + $list['skor4'] * 4;

                $header->lists()->create([
                    'no_sample' => $validated['no_sample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'nourut' => $list['nourut'],
                    'jm_batang' => $jm_batang,
                    'ppt' => $ppt,
                    'ppt_aktif' => $list['ppt_aktif'],
                    'pbt' => $pbt,
                    'pbt_aktif' => $list['pbt_aktif'],
                    'skor0' => $list['skor0'],
                    'skor1' => $list['skor1'],
                    'skor2' => $list['skor2'],
                    'skor3' => $list['skor3'],
                    'skor4' => $list['skor4'],
                    'per_ppt' => $jm_batang != 0 ? $ppt / $jm_batang : 0,
                    'per_ppt_aktif' => $jm_batang != 0 ? $list['ppt_aktif'] / $jm_batang : 0,
                    'per_pbt' => $jm_batang != 0 ? $pbt / $jm_batang : 0,
                    'per_pbt_aktif' => $jm_batang != 0 ? $list['pbt_aktif'] / $jm_batang : 0,
                    'sum_ni' => $sum_ni,
                    'int_rusak' => $jm_batang != 0 ? $sum_ni / ($jm_batang * 4) : 0,
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
                    'inputby' => Auth::user()->usernm,
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


    public function show($no_sample, $companycode, $tanggaltanam)
    {
        $hpt = DB::table('hpt_hdr')
            ->where('companycode', '=', session('companycode'))
            ->where('no_sample', $no_sample)
            ->where('companycode', $companycode)
            ->where('tanggaltanam', $tanggaltanam)
            ->first();

        if (!$hpt) {
            abort(404, 'HPT header not found');
        }

        $hptLists = DB::table('hpt_lst')
            ->leftJoin('hpt_hdr', function ($join) use ($hpt) {
                $join->on('hpt_lst.no_sample', '=', 'hpt_hdr.no_sample')
                    ->whereColumn('hpt_lst.companycode', '=', 'hpt_hdr.companycode')
                    ->whereColumn('hpt_lst.tanggaltanam', '=', 'hpt_hdr.tanggaltanam');
            })
            ->leftJoin('company', function ($join) {
                $join->on('hpt_hdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('hpt_hdr.blok', '=', 'blok.blok')
                    ->whereColumn('hpt_hdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('hpt_hdr.plot', '=', 'plot.plot')
                    ->whereColumn('hpt_hdr.companycode', '=', 'plot.companycode');
            })
            ->select(
                'hpt_lst.*',
                'hpt_hdr.varietas',
                'hpt_hdr.tglamat',
                'company.nama as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
            )
            ->where('hpt_lst.no_sample', $no_sample)
            ->where('hpt_lst.companycode', $companycode)
            ->where('hpt_lst.tanggaltanam', $tanggaltanam)
            ->orderBy('hpt_lst.createdat', 'desc')
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

    public function edit($no_sample, $companycode, $tanggaltanam)
    {
        $title = 'Edit Data';
        $header = HPTHeader::with(['lists' => function ($query) use ($no_sample, $companycode, $tanggaltanam) {
            $query->where('no_sample', $no_sample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam);
        }])
            ->where('no_sample', $no_sample)
            ->where('companycode', $companycode)
            ->where('tanggaltanam', $tanggaltanam)
            ->firstOrFail();
        $list = HPTList::where('no_sample', $no_sample)
            ->where('companycode', $companycode)
            ->where('tanggaltanam', $tanggaltanam)
            ->firstOrFail();
        $company = company::all();
        $mapping = Mapping::all();
        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('input.hpt.update', ['no_sample' => $no_sample, 'companycode' => $companycode, 'tanggaltanam' => $tanggaltanam]);

        if ($header->status === "Posted") {
            return redirect()->route('input.hpt.index')->with('success1', 'Data telah di posting, tidak dapat mengakses edit.');
        }

        return view('input.hpt.form', compact('buttonSubmit', 'header', 'list', 'company', 'mapping', 'title', 'method', 'url'));
    }

    public function update(Request $request, $no_sample, $companycode, $tanggaltanam)
    {
        $validated = $request->validate($this->requestValidated());

        DB::beginTransaction();

        try {
            $header = HPTHeader::where('no_sample', $no_sample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->firstOrFail();

            DB::table('hpt_hdr')
                ->where('no_sample', $no_sample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->update([
                    'no_sample' => $validated['no_sample'],
                    'companycode' => $validated['companycode'],
                    'blok' => $validated['blok'],
                    'plot' => $validated['plot'],
                    'idblokplot' => $validated['idblokplot'],
                    'varietas' => $validated['varietas'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tglamat' => $validated['tglamat'],
                    'updatedat' => now(),
                ]);

            $existingLists = HPTList::where('no_sample', $no_sample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->get(['nourut', 'inputby', 'createdat'])
                ->keyBy('nourut');

            DB::table('hpt_lst')
                ->where('no_sample', $no_sample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->delete();

            $listData = [];
            foreach ($validated['lists'] as $list) {
                $jm_batang = $list['skor0'] + $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $ppt = ($list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'] + $list['pupa_ppt']) + $list['kosong_ppt'];
                $pbt = $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $sum_ni = $list['skor0'] * 0 + $list['skor1'] * 1 + $list['skor2'] * 2 + $list['skor3'] * 3 + $list['skor4'] * 4;
                $listData[] = [
                    'no_sample' => $validated['no_sample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'nourut' => $list['nourut'],
                    'jm_batang' => $jm_batang,
                    'ppt' => $ppt,
                    'ppt_aktif' => $list['ppt_aktif'],
                    'pbt' => $pbt,
                    'pbt_aktif' => $list['pbt_aktif'],
                    'skor0' => $list['skor0'],
                    'skor1' => $list['skor1'],
                    'skor2' => $list['skor2'],
                    'skor3' => $list['skor3'],
                    'skor4' => $list['skor4'],
                    'per_ppt' => $jm_batang != 0 ? $ppt / $jm_batang : 0,
                    'per_ppt_aktif' => $jm_batang != 0 ? $list['ppt_aktif'] / $jm_batang : 0,
                    'per_pbt' => $jm_batang != 0 ? $pbt / $jm_batang : 0,
                    'per_pbt_aktif' => $jm_batang != 0 ? $list['pbt_aktif'] / $jm_batang : 0,
                    'sum_ni' => $sum_ni,
                    'int_rusak' => $jm_batang != 0 ? $sum_ni / ($jm_batang * 4) : 0,
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
                    'inputby' => $existingLists[$list['nourut']]['inputby'] ?? $header->inputby,
                    'createdat' => $existingLists[$list['nourut']]['createdat'] ?? $header->createdat,
                    'updatedat' => now(),
                ];
            }

            DB::table('hpt_lst')->insert($listData);

            DB::commit();

            return redirect()->route('input.hpt.index')
                ->with('success', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('input.hpt.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($no_sample, $companycode, $tanggaltanam)
    {
        DB::transaction(function () use ($no_sample, $companycode, $tanggaltanam) {
            $header = HPTHeader::where('no_sample', $no_sample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->firstOrFail();
            $list = HPTList::where('no_sample', $no_sample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam);

            $header->delete();
            $list->delete();
        });
        return redirect()->route('input.hpt.index')
            ->with('success', 'Data deleted successfully.');
    }

    public function excel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('hpt_lst')
            ->leftJoin('hpt_hdr', function ($join) {
                $join->on('hpt_lst.no_sample', '=', 'hpt_hdr.no_sample')
                    ->whereColumn('hpt_lst.companycode', '=', 'hpt_hdr.companycode')
                    ->whereColumn('hpt_lst.tanggaltanam', '=', 'hpt_hdr.tanggaltanam');
            })
            ->leftJoin('company', function ($join) {
                $join->on('hpt_hdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('hpt_hdr.blok', '=', 'blok.blok')
                    ->whereColumn('hpt_hdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('hpt_hdr.plot', '=', 'plot.plot')
                    ->whereColumn('hpt_hdr.companycode', '=', 'plot.companycode');
            })
            ->where('hpt_lst.companycode', session('companycode'))
            ->where('hpt_hdr.companycode', session('companycode'))
            ->select(
                'hpt_lst.*',
                'hpt_hdr.varietas',
                'hpt_hdr.tglamat',
                'company.nama as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
            )
            ->orderBy('hpt_lst.createdat', 'desc');


        if ($startDate) {
            $query->whereDate('hpt_lst.createdat', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('hpt_lst.createdat', '<=', $endDate);
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
        $sheet->setCellValue('I1', 'Tanggal Pengamatan');
        $sheet->setCellValue('J1', 'Bulan Pengamatan');
        $sheet->setCellValue('K1', 'No. Urut');
        $sheet->setCellValue('L1', 'Jumlah Batang');
        $sheet->setCellValue('M1', 'PPT');
        $sheet->setCellValue('N1', 'PBT');
        $sheet->setCellValue('O1', 'Skor 0');
        $sheet->setCellValue('P1', 'Skor 1');
        $sheet->setCellValue('Q1', 'Skor 2');
        $sheet->setCellValue('R1', 'Skor 3');
        $sheet->setCellValue('S1', 'Skor 4');
        $sheet->setCellValue('T1', '%PPT');
        $sheet->setCellValue('U1', '%PBT');
        $sheet->setCellValue('V1', 'Σni*vi');
        $sheet->setCellValue('W1', 'Intensitas Kerusakan');
        $sheet->setCellValue('X1', 'Telur PPT');
        $sheet->setCellValue('Y1', 'Larva PPT 1');
        $sheet->setCellValue('Z1', 'Larva PPT 2');
        $sheet->setCellValue('AA1', 'Larva PPT 3');
        $sheet->setCellValue('AB1', 'Larva PPT 4');
        $sheet->setCellValue('AC1', 'Pupa PPT');
        $sheet->setCellValue('AD1', 'Ngengat PPT');
        $sheet->setCellValue('AE1', 'Kosong PPT');
        $sheet->setCellValue('AF1', 'Telur PBT');
        $sheet->setCellValue('AG1', 'Larva PBT 1');
        $sheet->setCellValue('AH1', 'Larva PBT 2');
        $sheet->setCellValue('AI1', 'Larva PBT 3');
        $sheet->setCellValue('AJ1', 'Larva PBT 4');
        $sheet->setCellValue('AK1', 'Pupa PBT');
        $sheet->setCellValue('AL1', 'Ngengat PBT');
        $sheet->setCellValue('AM1', 'Kosong PBT');
        $sheet->setCellValue('AN1', 'DH');
        $sheet->setCellValue('AO1', 'DT');
        $sheet->setCellValue('AP1', 'KBP');
        $sheet->setCellValue('AQ1', 'KBB');
        $sheet->setCellValue('AR1', 'KP');
        $sheet->setCellValue('AS1', 'Cabuk');
        $sheet->setCellValue('AT1', 'Belalang');
        $sheet->setCellValue('AU1', 'Ul.Grayak');
        $sheet->setCellValue('AV1', 'BTG Terserang SMUT');
        $sheet->setCellValue('AW1', 'SMUT Stadia 1');
        $sheet->setCellValue('AX1', 'SMUT Stadia 2');
        $sheet->setCellValue('AY1', 'SMUT Stadia 3');

        $sheet->getStyle('A1:AY1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($hpt as $list) {

            $tanggaltanam = Carbon::parse($list->tanggaltanam);
            $umurTanam = $tanggaltanam->diffInMonths($now);

            $tglAmat = Carbon::parse($list->tglamat);
            $bulanPengamatan = $tglAmat->format('F');

            $sheet->setCellValue('A' . $row, $list->no_sample);
            $sheet->setCellValue('B' . $row, $list->compName);
            $sheet->setCellValue('C' . $row, $list->blokName);
            $sheet->setCellValue('D' . $row, $list->plotName);
            $sheet->setCellValue('E' . $row, $list->luasarea);
            $sheet->setCellValue('F' . $row, $tanggaltanam->format('Y-m-d'));
            $sheet->setCellValue('G' . $row, ceil($umurTanam) . ' Bulan');
            $sheet->setCellValue('H' . $row, $list->varietas);
            $sheet->setCellValue('I' . $row, $list->tglamat);
            $sheet->setCellValue('J' . $row, $bulanPengamatan);
            $sheet->setCellValue('K' . $row, $list->nourut);
            $sheet->setCellValue('L' . $row, $list->jm_batang);
            $sheet->setCellValue('M' . $row, $list->ppt);
            $sheet->setCellValue('N' . $row, $list->pbt);
            $sheet->setCellValue('O' . $row, $list->skor0);
            $sheet->setCellValue('P' . $row, $list->skor1);
            $sheet->setCellValue('Q' . $row, $list->skor2);
            $sheet->setCellValue('R' . $row, $list->skor3);
            $sheet->setCellValue('S' . $row, $list->skor4);
            $sheet->setCellValue('T' . $row, $list->per_ppt);
            $sheet->setCellValue('U' . $row, $list->per_pbt);
            $sheet->setCellValue('V' . $row, $list->sum_ni);
            $sheet->setCellValue('W' . $row, $list->int_rusak);
            $sheet->setCellValue('X' . $row, $list->telur_ppt);
            $sheet->setCellValue('Y' . $row, $list->larva_ppt1);
            $sheet->setCellValue('Z' . $row, $list->larva_ppt2);
            $sheet->setCellValue('AA' . $row, $list->larva_ppt3);
            $sheet->setCellValue('AB' . $row, $list->larva_ppt4);
            $sheet->setCellValue('AC' . $row, $list->pupa_ppt);
            $sheet->setCellValue('AD' . $row, $list->ngengat_ppt);
            $sheet->setCellValue('AE' . $row, $list->kosong_ppt);
            $sheet->setCellValue('AF' . $row, $list->telur_pbt);
            $sheet->setCellValue('AG' . $row, $list->larva_pbt1);
            $sheet->setCellValue('AH' . $row, $list->larva_pbt2);
            $sheet->setCellValue('AI' . $row, $list->larva_pbt3);
            $sheet->setCellValue('AJ' . $row, $list->larva_pbt4);
            $sheet->setCellValue('AK' . $row, $list->pupa_pbt);
            $sheet->setCellValue('AL' . $row, $list->ngengat_pbt);
            $sheet->setCellValue('AM' . $row, $list->kosong_pbt);
            $sheet->setCellValue('AN' . $row, $list->dh);
            $sheet->setCellValue('AO' . $row, $list->dt);
            $sheet->setCellValue('AP' . $row, $list->kbp);
            $sheet->setCellValue('AQ' . $row, $list->kbb);
            $sheet->setCellValue('AR' . $row, $list->kp);
            $sheet->setCellValue('AS' . $row, $list->cabuk);
            $sheet->setCellValue('AT' . $row, $list->belalang);
            $sheet->setCellValue('AU' . $row, $list->grayak);
            $sheet->setCellValue('AV' . $row, $list->serang_smut);
            $sheet->setCellValue('AW' . $row, $list->smut_stadia1);
            $sheet->setCellValue('AX' . $row, $list->smut_stadia2);
            $sheet->setCellValue('AY' . $row, $list->smut_stadia3);

            $sheet->getStyle('T' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->getStyle('U' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->getStyle('W' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
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
