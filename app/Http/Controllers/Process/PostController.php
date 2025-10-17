<?php

namespace App\Http\Controllers\Process;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class PostController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Process',
            'nav' => 'Posting',
        ]);
    }

    public function index(Request $request)
    {
        $title = "Posting";
        $search = $request->input('search', '');

        if ($request->has('start_date')) {
            session(['start_date_posting' => $request->start_date]);
        }
        if ($request->has('end_date')) {
            session(['end_date_posting' => $request->end_date]);
        }
        $startDate = session('start_date_posting');
        $endDate = session('end_date_posting');
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

        if ($request->has('posting')) {
            session(['posting' => $request->posting]);
        }
        $session = session('posting');
        $companysession = session('companycode');

        $table = $session === 'Agronomi' ? 'agrohdr' : 'hpthdr';

        $posts = DB::table($table)
            ->orderBy('createdat', 'desc')
            ->where('companycode', '=', $companysession)
            ->where('status', '=', 'Unposted')
            ->when($startDate, fn($query) => $query->whereDate('tanggalpengamatan', '>=', $startDate))
            ->when($endDate, fn($query) => $query->whereDate('tanggalpengamatan', '<=', $endDate));

        if (!empty($search)) {
            $posts->where(function ($query) use ($search) {
                $query->where('nosample', 'like', '%' . $search . '%')
                    ->orWhere('idblokplot', 'like', '%' . $search . '%')
                    ->orWhere('varietas', 'like', '%' . $search . '%')
                    ->orWhere('kat', 'like', '%' . $search . '%');
            });
        }
        $posts = $posts->paginate($perPage);

        foreach ($posts as $index => $item) {
            if (!empty($item->tanggaltanam)) {
                $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
            } else {
                $item->umur_tanam = null;
            }

            $item->no = ($posts->currentPage() - 1) * $posts->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('process.posting.index', compact('posts', 'perPage', 'startDate', 'endDate', 'title', 'search'));
        }
        return view('process.posting.index', compact('posts', 'perPage', 'startDate', 'endDate', 'title', 'search'));
    }

    public function postSession(Request $request)
    {
        $request->validate([
            'posting' => 'required|string',
        ]);

        session(['posting' => $request->posting]);

        return redirect()->route('process.posting');
    }

    public function posting(Request $request)
    {
        $selectedItems = json_decode($request->selected_items, true);
        $selectedItems = array_map(function ($item) {
            $parts = explode(',', $item);
            return [
                'nosample' => $parts[0] ?? null,
                'companycode' => $parts[1] ?? null,
                'tanggalpengamatan' => $parts[2] ?? null,
            ];
        }, $selectedItems);

        if (!$selectedItems) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $tables = session('posting') === 'Agronomi'
            ? ['agrohdr', 'agrolst']
            : ['hpthdr', 'hptlst'];

        foreach ($tables as $table) {
            $updateData = [
                'status' => 'Posted',
                'count' => DB::raw('count + 1'),
            ];

            if (in_array($table, ['agrohdr', 'hpthdr'])) {
                $updateData['tanggalposting'] = now()->toDateString();
            }

            DB::table($table)
                ->whereIn('nosample', array_column($selectedItems, 'nosample'))
                ->whereIn('companycode', array_column($selectedItems, 'companycode'))
                ->whereIn('tanggalpengamatan', array_column($selectedItems, 'tanggalpengamatan'))
                ->update($updateData);
        }

        return redirect()->back()->with('success1', 'Data berhasil diposting.');
    }
}
