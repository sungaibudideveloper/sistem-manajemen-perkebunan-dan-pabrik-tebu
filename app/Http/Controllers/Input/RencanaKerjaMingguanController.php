<?php

namespace App\Http\Controllers\Input;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class RencanaKerjaMingguanController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Transaction',
            'nav' => 'Rencana Kerja Mingguan',
        ]);
    }

    protected function requestValidated(): array
    {
        return [
            'rkmno' => 'required',
            'rkmdate' => 'required',
            'startdate' => 'required',
            'enddate' => 'required',
            'activitycode' => 'required',
            'lists.*.blok' => 'required',
            'lists.*.plot' => 'required',
            'lists.*.totalluasactual' => 'required',
            'lists.*.totalestimasi' => 'required',
            // 'lists.*.totalhasil' => 'required',
            // 'lists.*.totalsisa' => 'required',
        ];
    }

    public function index(Request $request)
    {
        $title = 'Rencana Kerja Mingguan';

        $search = $request->input('search', '');
        $isClosing = $request->input('isclosing', 0);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $userid = Auth::user()->userid;

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $querys = DB::table('rkmhdr')
            // ->leftJoin('rkmlst', function ($join) {
            //     $join->on('rkmhdr.rkmno', '=', 'rkmlst.rkmno')
            //         ->whereColumn('rkmhdr.companycode', '=', 'rkmlst.companycode');
            // })
            ->join('activity', 'activity.activitycode', '=', 'rkmhdr.activitycode')
            ->where('rkmhdr.companycode', '=', session('companycode'))
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('rkmhdr.rkmdate', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('rkmhdr.rkmdate', '<=', $endDate);
            });

        if ($userid != 'Admin') {
            $querys->where('rkmhdr.inputby', '=', $userid);
        }
        // ->where('rkmlst.companycode', '=', session('companycode'))
        // ->where('rkmhdr.isclosing', '=', $isClosing);
        if (!empty($search)) {
            $querys->where(function ($query) use ($search) {
                $query->where('rkmhdr.rkmno', 'like', '%' . $search . '%')
                    ->orWhere('rkmhdr.activitycode', 'like', '%' . $search . '%');
            });
        }

        $rkm = $querys->select('rkmhdr.*', 'activity.activityname')
            ->orderBy('rkmdate', 'desc')
            ->orderBy('createdat', 'desc')
            ->paginate($perPage);

        foreach ($rkm as $index => $item) {
            $item->no = ($rkm->currentPage() - 1) * $rkm->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('input.rkm.index', compact('title', 'search', 'perPage', 'rkm'));
        }

        return view('input.rkm.index', compact('title', 'search', 'perPage', 'rkm'));
    }

    public function create(Request $request)
    {
        $title = "Create RKM";
        $activity = DB::table('activity')->orderBy('activitycode', 'asc')->get();
        $bloks = DB::table('blok')->where('companycode', '=', session('companycode'))->orderBy('blok', 'asc')->get();
        $method = 'POST';
        $url = route('input.rencana-kerja-mingguan.store');
        $buttonSubmit = 'Submit';
        $selectedDate = $request->input('targetDate');

        // if (!$selectedDate) {
        //     return redirect()->route('input.rencana-kerja-mingguan.index')
        //         ->with('error', 'Silakan pilih tanggal terlebih dahulu');
        // }

        if (!$this->validateDateRange($selectedDate)) {
            return redirect()->route('input.rencana-kerja-mingguan.index')
                ->with('error', 'Tanggal harus dalam rentang hari ini sampai 7 hari ke depan');
        }

        $targetDate = Carbon::parse($selectedDate);
        $rkmno = $this->generatePreviewRkmNo($targetDate, session('companycode'));
        return view('input.rkm.form', compact('buttonSubmit', 'activity', 'title', 'method', 'url', 'rkmno', 'selectedDate', 'bloks'));
    }

    private function generatePreviewRkmNo($targetDate, $companycode)
    {
        $day = $targetDate->format('d');
        $month = $targetDate->format('m');
        $year = $targetDate->format('y');

        $lastRkm = DB::table('rkmhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkmdate', $targetDate)
            ->where('rkmno', 'like', "RKM{$day}{$month}%" . $year)
            ->orderBy(DB::raw('CAST(SUBSTRING(rkmno, 8, 2) AS UNSIGNED)'), 'desc')
            ->first();

        $newNumber = $lastRkm ? str_pad(((int) substr($lastRkm->rkmno, 7, 2)) + 1, 2, '0', STR_PAD_LEFT) : '01';
        return "RKM{$day}{$month}{$newNumber}{$year}";
    }

    private function validateDateRange($selectedDate)
    {
        $targetDate = Carbon::parse($selectedDate);
        $today = Carbon::today();
        $maxDate = Carbon::today()->addDays(7);

        return $targetDate->gte($today) && $targetDate->lte($maxDate);
    }

    public function getPlot($blok)
    {
        $comp = session('companycode');
        $plots = DB::table('plot')
            ->where('companycode', $comp)
            // ->where('tgl2', '=', null)
            ->where('plot', 'like', $blok . '%')
            ->orderByRaw("LEFT(plot, 1), CAST(SUBSTRING(plot, 2) AS UNSIGNED)")
            ->pluck('plot');

        return response()->json($plots);
    }

    public function getData(Request $request)
    {
        $plot = $request->input('plot');
        $luas = DB::table('plot')->where('plot', $plot)
            ->where('companycode', session('companycode'))->first();

        if ($luas) {
            return response()->json([
                'luasarea' => $luas->luasarea,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());

        $existsInHeader = DB::table('rkmhdr')->where('rkmno', $request->rkmno)
            ->where('companycode', $request->companycode)
            ->exists();

        $existsInLists = DB::table('rkmlst')->where('rkmno', $request->rkmno)
            ->where('companycode', $request->companycode)
            ->exists();

        if ($existsInHeader || $existsInLists) {
            return back()->with([
                'success1' => 'Data sudah ada di salah satu tabel, silahkan coba dengan data yang berbeda.',
            ])->withInput();
        }

        // dd($validated);

        DB::beginTransaction();

        try {

            DB::table('rkmhdr')->insert([
                'rkmno' => $validated['rkmno'],
                'rkmdate' => $validated['rkmdate'],
                'companycode' => session('companycode'),
                'startdate' => $validated['startdate'],
                'enddate' => $validated['enddate'],
                'activitycode' => $validated['activitycode'],
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updateby' => Auth::user()->userid,
                'updatedat' => now(),
            ]);

            foreach ($validated['lists'] as $list) {

                DB::table('rkmlst')->insert([
                    'rkmno' => $validated['rkmno'],
                    'companycode' => session('companycode'),
                    'blok' => $list['blok'],
                    'plot' => $list['plot'],
                    'totalluasactual' => $list['totalluasactual'],
                    'totalestimasi' => $list['totalestimasi'],
                ]);
            }

            DB::commit();
            return redirect()->route('input.rencana-kerja-mingguan.index')
                ->with('success1', 'Data created successfully.');
        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()->route('input.rencana-kerja-mingguan.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function show($rkmno)
    {
        $companyCode = session('companycode');

        $rkm = DB::table('rkmhdr')
            ->where('companycode', $companyCode)
            ->where('rkmno', $rkmno)
            ->first();

        if (!$rkm) {
            abort(404, 'RKM header not found');
        }

        $rkmLists = DB::table('rkmhdr as a')
            ->leftJoin('rkmlst as b', function ($join) {
                $join->on('a.companycode', '=', 'b.companycode')
                    ->on('a.rkmno', '=', 'b.rkmno');
            })
            ->leftJoin('lkhhdr as c', function ($join) {
                $join->on('a.companycode', '=', 'b.companycode')
                    ->on('a.activitycode', '=', 'c.activitycode')
                    ->whereBetween('c.lkhdate', [DB::raw('a.startdate'), DB::raw('a.enddate')]);
            })
            ->leftJoin('lkhdetailplot as d', function ($join) {
                $join->on('c.lkhno', '=', 'd.lkhno')
                    ->on('a.companycode', '=', 'b.companycode')
                    ->on('b.plot', '=', 'd.plot');
            })
            ->leftJoin('activity as act', 'a.activitycode', '=', 'act.activitycode')
            ->select(
                'a.rkmno',
                'a.startdate',
                'a.enddate',
                'a.activitycode',
                'act.activityname',
                'b.totalestimasi',
                'b.blok',
                'b.plot',
                'b.totalluasactual',
                DB::raw('SUM(d.luashasil) AS hasil'),
                DB::raw('b.totalestimasi - SUM(d.luashasil) AS sisa')
            )
            ->where('a.companycode', $companyCode)
            ->where('a.rkmno', $rkmno)
            ->groupBy(
                'a.rkmno',
                'a.startdate',
                'a.enddate',
                'a.activitycode',
                'act.activityname',
                'b.totalestimasi',
                'b.blok',
                'b.plot',
                'b.totalluasactual'
            )
            ->get();

        foreach ($rkmLists as $index => $item) {
            $item->no = $index + 1;
        }

        return response()->json($rkmLists);
    }


    public function edit($rkmno)
    {
        $title = 'Edit Data';
        $activity = DB::table('activity')->orderBy('activitycode', 'asc')->get();
        $bloks = DB::table('blok')->where('companycode', '=', session('companycode'))->orderBy('blok', 'asc')->get();

        $header = DB::table('rkmhdr')->where('rkmno', $rkmno)
            ->where('companycode', session('companycode'))
            ->first();
        $lists = DB::table('rkmlst')
            ->where('rkmno', $rkmno)
            ->where('companycode', session('companycode'))
            ->get();
        $list = $lists->first();
        $header->lists = $lists;

        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('input.rencana-kerja-mingguan.update', ['rkmno' => $rkmno]);

        if ($header->isclosing === "1") {
            return redirect()->route('input.rencana-kerja-mingguan.index')->with('success1', 'Data telah di closing, tidak dapat di edit.');
        }

        return view('input.rkm.form', compact('buttonSubmit', 'header', 'list', 'title', 'method', 'url', 'activity', 'bloks'));
    }

    public function update(Request $request, $rkmno)
    {
        $validated = $request->validate($this->requestValidated());

        DB::beginTransaction();

        try {
            DB::table('rkmhdr')
                ->where('rkmno', $rkmno)
                ->where('companycode', session('companycode'))
                ->update([
                    'rkmno' => $validated['rkmno'],
                    'rkmdate' => $validated['rkmdate'],
                    'companycode' => session('companycode'),
                    'startdate' => $validated['startdate'],
                    'enddate' => $validated['enddate'],
                    'activitycode' => $validated['activitycode'],
                    'updateby' => Auth::user()->userid,
                    'updatedat' => now(),
                ]);

            $lists = DB::table('rkmlst')
                ->where('rkmno', $rkmno)
                ->where('companycode', session('companycode'));

            $lists->delete();

            foreach ($validated['lists'] as $list) {
                $data = [
                    'rkmno' => $validated['rkmno'],
                    'companycode' => session('companycode'),
                    'blok' => $list['blok'],
                    'plot' => $list['plot'],
                    'totalluasactual' => $list['totalluasactual'],
                    'totalestimasi' => $list['totalestimasi'],
                ];
                DB::table('rkmlst')->insert($data);
            }

            DB::commit();

            return redirect()->route('input.rencana-kerja-mingguan.index')
                ->with('success1', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('input.rencana-kerja-mingguan.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($rkmno)
    {
        DB::transaction(function () use ($rkmno) {
            DB::table('rkmhdr')
                ->where('rkmno', $rkmno)
                ->where('companycode', session('companycode'))
                ->delete();
            DB::table('rkmlst')
                ->where('rkmno', $rkmno)
                ->where('companycode', session('companycode'))
                ->delete();
        });
        return redirect()->route('input.rencana-kerja-mingguan.index')
            ->with('success1', 'Data deleted successfully.');
    }
}
