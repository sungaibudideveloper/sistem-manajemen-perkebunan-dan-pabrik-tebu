<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\HPTHeader;
use Illuminate\Http\Request;
use App\Models\AgronomiHeader;
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

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $companyArray = explode(',', Auth::user()->userComp->kd_comp);

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $session = session('posting');
        $dropdownValue = session('dropdown_value');

        $model = $session === 'Agronomi' ? AgronomiHeader::class : HPTHeader::class;

        $posts = $model::orderBy('created_at', 'desc')
            ->with(['lists', 'perusahaan'])
            ->where('kd_comp', '=', $dropdownValue)
            ->where('status', '=', 'Unposted')
            ->when($startDate, fn($query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->whereDate('created_at', '<=', $endDate))
            ->paginate($perPage);

        foreach ($posts as $index => $item) {
            if (!empty($item->tgltanam)) {
                $item->umur_tanam = Carbon::parse($item->tgltanam)->diffInMonths(Carbon::now());
            } else {
                $item->umur_tanam = null;
            }

            $item->no = ($posts->currentPage() - 1) * $posts->perPage() + $index + 1;
        }

        return view('process.posting.index', compact('posts', 'perPage', 'startDate', 'endDate', 'title'));
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
                'no_sample' => $parts[0] ?? null,
                'kd_comp'   => $parts[1] ?? null,
                'tgltanam'  => $parts[2] ?? null,
            ];
        }, $selectedItems);

        if (!$selectedItems) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $tables = session('posting') === 'Agronomi'
            ? ['agro_hdr', 'agro_lst']
            : ['hpt_hdr', 'hpt_lst'];

        foreach ($tables as $table) {
            DB::table($table)
                ->whereIn('no_sample', array_column($selectedItems, 'no_sample'))
                ->whereIn('kd_comp', array_column($selectedItems, 'kd_comp'))
                ->whereIn('tgltanam', array_column($selectedItems, 'tgltanam'))
                ->update(['status' => 'Posted', 'count' => DB::raw('count + 1')]);
        }

        return redirect()->back()->with('success1', 'Data berhasil diposting.');
    }
}
