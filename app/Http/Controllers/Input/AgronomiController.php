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
use App\Models\Notification;

class AgronomiController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Input Data',
            'nav' => 'Agronomi',
            'routeName' => route('input.agronomi.index'),
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
            'lists.*.jumlahbatang' => 'required',
            'lists.*.pan_gap' => 'required',
            'lists.*.ph_tanah' => 'required|numeric',
            'lists.*.ktk_gulma' => 'required',
            'lists.*.t_primer' => 'required',
            'lists.*.t_sekunder' => 'required',
            'lists.*.t_tersier' => 'required',
            'lists.*.t_kuarter' => 'required',
            'lists.*.d_primer' => 'required',
            'lists.*.d_sekunder' => 'required',
            'lists.*.d_tersier' => 'required',
            'lists.*.d_kuarter' => 'required',
        ];
    }
    public function index(Request $request)
    {
        $title = "Daftar Agronomi";
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

        $agronomi = DB::table('agrohdr')
            ->join('company', 'agrohdr.companycode', '=', 'company.companycode')
            ->where('agrohdr.companycode', '=', session('companycode'))
            ->where('agrohdr.closingperiode', '=', 'F')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('agrohdr.tanggalpengamatan', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('agrohdr.tanggalpengamatan', '<=', $endDate);
            });

        if (!empty($search)) {
            $agronomi->where(function ($query) use ($search) {
                $query->where('agrohdr.idblokplot', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.nosample', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.varietas', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.plot', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.kat', 'like', '%' . $search . '%');
            });

        }
        $agronomi = $agronomi->select(
            'agrohdr.*',
            'company.name as nama_comp'
        )
            ->orderBy('agrohdr.createdat', 'desc')
            ->paginate($perPage);

        foreach ($agronomi as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
        }

        foreach ($agronomi as $index => $item) {
            $item->no = ($agronomi->currentPage() - 1) * $agronomi->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('input.agronomi.index', compact('agronomi', 'perPage', 'startDate', 'endDate', 'title', 'search'));
        }

        return view('input.agronomi.index', compact('agronomi', 'perPage', 'startDate', 'endDate', 'title', 'search'));
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
        $mapping = DB::table('mapping')
            ->where('companycode', '=', session('companycode'))
            ->orderByRaw("CAST(idblokplot AS UNSIGNED)")
            ->get();
        $method = 'POST';
        $url = route('input.agronomi.handle');
        $buttonSubmit = 'Create';
        return view('input.agronomi.form', compact('buttonSubmit', 'mapping', 'title', 'method', 'url'));
    }

    public function getFieldByMapping(Request $request)
    {
        $idblokplot = $request->input('idblokplot');
        $mapping = DB::table('mapping')->where('idblokplot', $idblokplot)
            ->where('companycode', session('companycode'))->first();

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

        $data = DB::table('agrohdr')
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

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());

        $existsInHeader = DB::table('agrohdr')->where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggalpengamatan', $request->tanggalpengamatan)
            ->exists();

        $existsInLists = DB::table('agrolst')->where('nosample', $request->nosample)
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

            DB::table('agrohdr')->insert([
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

            $totalPerGerminasi = 0;
            $totalPerGulma = 0;
            $count = count($validated['lists']);

            foreach ($validated['lists'] as $list) {
                $per_gap = $list['pan_gap'] / 1000;
                $per_germinasi = 1 - $per_gap;
                $per_gulma = $list['ktk_gulma'] ? $list['ktk_gulma'] / 16 : 0;

                DB::table('agrolst')->insert([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $list['jumlahbatang'],
                    'pan_gap' => $list['pan_gap'],
                    'per_gap' => $per_gap,
                    'per_germinasi' => $per_germinasi,
                    'ph_tanah' => $list['ph_tanah'],
                    'populasi' => round($list['jumlahbatang'] / 10),
                    'ktk_gulma' => $list['ktk_gulma'],
                    'per_gulma' => $per_gulma,
                    't_primer' => $list['t_primer'],
                    't_sekunder' => $list['t_sekunder'],
                    't_tersier' => $list['t_tersier'],
                    't_kuarter' => $list['t_kuarter'],
                    'd_primer' => $list['d_primer'],
                    'd_sekunder' => $list['d_sekunder'],
                    'd_tersier' => $list['d_tersier'],
                    'd_kuarter' => $list['d_kuarter'],
                    'inputby' => Auth::user()->userid,
                    'createdat' => now(),
                    'updatedat' => now(),
                ]);

                $totalPerGerminasi += $per_germinasi;
                $totalPerGulma += $per_gulma;
            }

            $avgPerGerminasi = $totalPerGerminasi / $count;
            $avgPerGulma = $totalPerGulma / $count;

            $umurTanam = Carbon::parse($validated['tanggaltanam'])->diffInMonths(Carbon::now());

            if ($avgPerGerminasi < 0.9 && $umurTanam == 1.0 || $avgPerGulma > 0.25) {
                Notification::createForAgronomi([
                    'plot' => $validated['plot'],
                    'companycode' => $validated['companycode'],
                    'condition' => [
                        'germinasi' => $avgPerGerminasi,
                        'gulma' => $avgPerGulma,
                        'umur' => $umurTanam,
                    ]
                ]);
            }

            DB::commit();
            return redirect()->back()
                ->with('success1', 'Data created successfully.');
        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()->route('input.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function show($nosample, $companycode, $tanggalpengamatan)
    {

        $agronomi = DB::table('agrohdr')
            ->where('companycode', '=', session('companycode'))
            ->where('nosample', $nosample)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->first();

        if (!$agronomi) {
            abort(404, 'Agronomi header not found');
        }

        $agronomiLists = DB::table('agrolst')
            ->leftJoin('agrohdr', function ($join) {
                $join->on('agrolst.nosample', '=', 'agrohdr.nosample')
                    ->whereColumn('agrolst.companycode', '=', 'agrohdr.companycode')
                    ->whereColumn('agrolst.tanggalpengamatan', '=', 'agrohdr.tanggalpengamatan');
            })
            ->leftJoin('company', function ($join) {
                $join->on('agrohdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agrohdr.blok', '=', 'blok.blok')
                    ->whereColumn('agrohdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('agrohdr.plot', '=', 'plot.plot')
                    ->whereColumn('agrohdr.companycode', '=', 'plot.companycode');
            })
            ->select(
                'agrolst.*',
                'agrohdr.varietas',
                'agrohdr.kat',
                'agrohdr.tanggaltanam',
                'company.name as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
                'plot.jaraktanam',
            )
            ->where('agrolst.nosample', $nosample)
            ->where('agrolst.companycode', $companycode)
            ->where('agrolst.tanggalpengamatan', $tanggalpengamatan)
            ->orderBy('agrolst.nourut', 'asc')
            ->get();

        $now = Carbon::now();

        $agronomiLists = $agronomiLists->map(function ($item) use ($now) {
            $tgl_tanam = Carbon::parse($item->tanggaltanam);
            $item->umur_tanam = $tgl_tanam->diffInMonths($now);
            return $item;
        });

        foreach ($agronomiLists as $index => $item) {
            $item->no = $index + 1;
        }

        return response()->json($agronomiLists);
    }

    public function edit($nosample, $companycode, $tanggalpengamatan)
    {
        $title = 'Edit Data';
        $header = DB::table('agrohdr')->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->first();
        $lists = DB::table('agrolst')
            ->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->get();
        $list = $lists->first();
        $header->lists = $lists;

        $company = DB::table('company')->get();
        $mapping = DB::table('mapping')->get();
        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('input.agronomi.update', ['nosample' => $nosample, 'companycode' => $companycode, 'tanggalpengamatan' => $tanggalpengamatan]);

        if ($header->status === "Posted") {
            return redirect()->route('input.agronomi.index')->with('success1', 'Data telah di posting, tidak dapat mengakses edit.');
        }

        return view('input.agronomi.form', compact('buttonSubmit', 'header', 'list', 'company', 'mapping', 'title', 'method', 'url'));
    }

    public function update(Request $request, $nosample, $companycode, $tanggalpengamatan)
    {
        $validated = $request->validate($this->requestValidated());

        DB::beginTransaction();

        try {
            DB::table('agrohdr')
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

            $lists = DB::table('agrolst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan);

            $saved = DB::table('agrolst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->first();

            $createdAt = $saved->createdat;
            $userInput = $saved->inputby;

            $lists->delete();

            foreach ($validated['lists'] as $list) {
                $data = [
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $list['jumlahbatang'],
                    'pan_gap' => $list['pan_gap'],
                    'per_gap' => $list['pan_gap'] / 1000,
                    'per_germinasi' => 1 - ($list['pan_gap'] / 1000),
                    'ph_tanah' => $list['ph_tanah'],
                    'populasi' => $list['jumlahbatang'] / 10,
                    'ktk_gulma' => $list['ktk_gulma'],
                    'per_gulma' => $list['ktk_gulma'] ? $list['ktk_gulma'] / 16 : 0,
                    't_primer' => $list['t_primer'],
                    't_sekunder' => $list['t_sekunder'],
                    't_tersier' => $list['t_tersier'],
                    't_kuarter' => $list['t_kuarter'],
                    'd_primer' => $list['d_primer'],
                    'd_sekunder' => $list['d_sekunder'],
                    'd_tersier' => $list['d_tersier'],
                    'd_kuarter' => $list['d_kuarter'],
                    'inputby' => $userInput,
                    'createdat' => $createdAt,
                    'updatedat' => now(),
                ];
                DB::table('agrolst')
                    ->where('nosample', $nosample)
                    ->where('companycode', $companycode)
                    ->where('tanggalpengamatan', $tanggalpengamatan)
                    ->where('nourut', $list['nourut'])
                    ->insert($data);
            }

            DB::commit();

            return redirect()->route('input.agronomi.index')
                ->with('success1', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('input.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($nosample, $companycode, $tanggalpengamatan)
    {
        DB::transaction(function () use ($nosample, $companycode, $tanggalpengamatan) {
            DB::table('agrohdr')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->delete();
            DB::table('agrolst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->delete();
        });
        return redirect()->route('input.agronomi.index')
            ->with('success', 'Data deleted successfully.');
    }

    public function excel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('agrolst')
            ->leftJoin('agrohdr', function ($join) {
                $join->on('agrolst.nosample', '=', 'agrohdr.nosample')
                    ->whereColumn('agrolst.companycode', '=', 'agrohdr.companycode')
                    ->whereColumn('agrolst.tanggalpengamatan', '=', 'agrohdr.tanggalpengamatan');
            })
            ->leftJoin('company', function ($join) {
                $join->on('agrohdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agrohdr.blok', '=', 'blok.blok')
                    ->whereColumn('agrohdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('agrohdr.plot', '=', 'plot.plot')
                    ->whereColumn('agrohdr.companycode', '=', 'plot.companycode');
            })
            ->where('agrolst.companycode', session('companycode'))
            ->where('agrohdr.companycode', session('companycode'))
            ->where('agrohdr.status', '=', 'Posted')
            ->where('agrolst.status', '=', 'Posted')
            ->select(
                'agrolst.*',
                'agrohdr.varietas',
                'agrohdr.kat',
                'agrohdr.tanggaltanam',
                'company.name as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luas_area',
                'plot.jarak_tanam',
            )
            ->orderBy('agrohdr.tanggalpengamatan', 'desc');


        if ($startDate) {
            $query->whereDate('agrohdr.tanggalpengamatan', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('agrohdr.tanggalpengamatan', '<=', $endDate);
        }
        $agronomi = $query->get();

        $now = Carbon::now();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No. Sample');
        $sheet->setCellValue('B1', 'Kebun');
        $sheet->setCellValue('C1', 'Blok');
        $sheet->setCellValue('D1', 'Plot');
        $sheet->setCellValue('E1', 'Luas');
        $sheet->setCellValue('F1', 'Varietas');
        $sheet->setCellValue('G1', 'Kategori');
        $sheet->setCellValue('H1', 'Tanggal Tanam');
        $sheet->setCellValue('I1', 'Umur Tanam');
        $sheet->setCellValue('J1', 'Jarak Tanam');
        $sheet->setCellValue('K1', 'Tanggal Pengamatan');
        $sheet->setCellValue('L1', 'Bulan Pengamatan');
        $sheet->setCellValue('M1', 'No. Urut');
        $sheet->setCellValue('N1', 'Jumlah Batang');
        $sheet->setCellValue('O1', 'Panjang GAP');
        $sheet->setCellValue('P1', '%GAP');
        $sheet->setCellValue('Q1', '%Germinasi');
        $sheet->setCellValue('R1', 'pH Tanah');
        $sheet->setCellValue('S1', 'Populasi');
        $sheet->setCellValue('T1', 'Kotak Gulma');
        $sheet->setCellValue('U1', '%Penutupan Gulma');
        $sheet->setCellValue('V1', 'Tinggi Primer');
        $sheet->setCellValue('W1', 'Tinggi Sekunder');
        $sheet->setCellValue('X1', 'Tinggi Tersier');
        $sheet->setCellValue('Y1', 'Tinggi Kuarter');
        $sheet->setCellValue('Z1', 'Diameter Primer');
        $sheet->setCellValue('AA1', 'Diameter Sekunder');
        $sheet->setCellValue('AB1', 'Diameter Tersier');
        $sheet->setCellValue('AC1', 'Diameter Kuarter');

        $sheet->getStyle('A1:AC1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($agronomi as $list) {

            $tanggaltanam = Carbon::parse($list->tanggaltanam);
            $umurTanam = $tanggaltanam->diffInMonths($now);

            $tanggalpengamatan = Carbon::parse($list->tanggalpengamatan);
            $bulanPengamatan = $tanggalpengamatan->format('F');

            $sheet->setCellValue('A' . $row, $list->nosample);
            $sheet->setCellValue('B' . $row, $list->compName);
            $sheet->setCellValue('C' . $row, $list->blokName);
            $sheet->setCellValue('D' . $row, $list->plotName);
            $sheet->setCellValue('E' . $row, $list->luas_area);
            $sheet->setCellValue('F' . $row, $list->varietas);
            $sheet->setCellValue('G' . $row, $list->kat);
            $sheet->setCellValue('H' . $row, $tanggaltanam->format('Y-m-d'));
            $sheet->setCellValue('I' . $row, round($umurTanam) . ' Bulan');
            $sheet->setCellValue('J' . $row, $list->jarak_tanam);
            $sheet->setCellValue('K' . $row, $list->tanggalpengamatan);
            $sheet->setCellValue('L' . $row, $bulanPengamatan);
            $sheet->setCellValue('M' . $row, $list->nourut);
            $sheet->setCellValue('N' . $row, $list->jumlahbatang);
            $sheet->setCellValue('O' . $row, $list->pan_gap);
            $sheet->setCellValue('P' . $row, $list->per_gap);
            $sheet->setCellValue('Q' . $row, $list->per_germinasi);
            $sheet->setCellValue('R' . $row, $list->ph_tanah);
            $sheet->setCellValue('S' . $row, $list->populasi);
            $sheet->setCellValue('T' . $row, $list->ktk_gulma);
            $sheet->setCellValue('U' . $row, $list->per_gulma);
            $sheet->setCellValue('V' . $row, $list->t_primer);
            $sheet->setCellValue('W' . $row, $list->t_sekunder);
            $sheet->setCellValue('X' . $row, $list->t_tersier);
            $sheet->setCellValue('Y' . $row, $list->t_kuarter);
            $sheet->setCellValue('Z' . $row, $list->d_primer);
            $sheet->setCellValue('AA' . $row, $list->d_sekunder);
            $sheet->setCellValue('AB' . $row, $list->d_tersier);
            $sheet->setCellValue('AC' . $row, $list->d_kuarter);

            $sheet->getStyle('P' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->getStyle('Q' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->getStyle('U' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        if ($startDate && $endDate) {
            $filename = "AgronomiReport_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $filename = "AgronomiReport.xlsx";
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
