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

class UnpostController extends Controller
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
        $title = "Unposting";

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

        $session = session('unposting');
        $dropdownValue = session('dropdown_value');

        $model = $session === 'Agronomi' ? AgronomiHeader::class : HPTHeader::class;

        $posts = $model::orderBy('createdat', 'desc')
            ->with(['lists', 'company'])
            ->where('companycode', '=', $dropdownValue)
            ->where('status', '=', 'Posted')
            ->when($startDate, fn($query) => $query->whereDate('createdat', '>=', $startDate))
            ->when($endDate, fn($query) => $query->whereDate('createdat', '<=', $endDate))
            ->paginate($perPage);

        foreach ($posts as $index => $item) {
            if (!empty($item->tanggaltanam)) {
                $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
            } else {
                $item->umur_tanam = null;
            }

            $item->no = ($posts->currentPage() - 1) * $posts->perPage() + $index + 1;
        }

        return view('process.unposting.index', compact('posts', 'perPage', 'startDate', 'endDate', 'title'));
    }

    public function unpostSession(Request $request)
    {
        $request->validate([
            'unposting' => 'required|string',
        ]);

        session(['unposting' => $request->unposting]);

        return redirect()->route('process.unposting');
    }

    public function unposting(Request $request)
    {
        $selectedItems = json_decode($request->selected_items, true);
        $selectedItems = array_map(function ($item) {
            $parts = explode(',', $item);
            return [
                'no_sample' => $parts[0] ?? null,
                'companycode'   => $parts[1] ?? null,
                'tanggaltanam'  => $parts[2] ?? null,
            ];
        }, $selectedItems);

        if (!$selectedItems) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $tables = session('unposting') === 'Agronomi'
            ? ['agro_hdr', 'agro_lst']
            : ['hpt_hdr', 'hpt_lst'];

        foreach ($tables as $table) {
            DB::table($table)
                ->whereIn('no_sample', array_column($selectedItems, 'no_sample'))
                ->whereIn('companycode', array_column($selectedItems, 'companycode'))
                ->whereIn('tanggaltanam', array_column($selectedItems, 'tanggaltanam'))
                ->update(['status' => 'Unposted']);
        }

        return redirect()->back()->with('success1', 'Data telah di unposting.');
    }
}
