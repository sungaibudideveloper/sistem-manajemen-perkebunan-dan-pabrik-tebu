<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Jabatan;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $query = Approval::with([
            'jabatanApproval1',
            'jabatanApproval2',
            'jabatanApproval3',
        ]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('activitycode', 'like', "%{$search}%")
                  ->orWhere('companycode',  'like', "%{$search}%");
            });
        }

        $approval = $query
            ->orderBy('category')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);
        $jabatan = Jabatan::orderBy('namajabatan','asc')->get();

        return view('masterdata.approval.index', [
            'approval'   => $approval,
            'title'      => 'Data Approval',
            'navbar'     => 'Master',
            'nav'        => 'Approval',
            'perPage'    => $perPage,
            'search'     => $search,
            'jabatan'    => $jabatan
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'companycode'        => 'required|string|max:4',
            'category'           => 'required|string|max:50',
            'jumlahapproval'     => 'required|integer',
            'idjabatanapproval1' => 'nullable|integer',
            'idjabatanapproval2' => 'nullable|integer',
            'idjabatanapproval3' => 'nullable|integer',
        ]);

        $exists = Approval::where('companycode', $request->companycode)->where('category', $request->category)->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'activitycode' => 'Duplicate Entry, Approval already exists'
                ]);
        }
        $lastId = Approval::where('companycode', session('companycode'))->max('id');
        Approval::create([
            'id'                 => $lastId+1,
            'companycode'        => $request->companycode,
            'category'           => $request->category,
            'jumlahapproval'     => $request->jumlahapproval,
            'idjabatanapproval1' => $request->idjabatanapproval1,
            'idjabatanapproval2' => $request->jumlahapproval >= 2 ? $request->idjabatanapproval2 : NULL,
            'idjabatanapproval3' => $request->jumlahapproval >= 3 ? $request->idjabatanapproval3 : NULL,
            'inputby'      => Auth::user()->userid,
            'createdat'    => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $category)
    {
        $approval = Approval::where([
            ['companycode',  $companycode],
            ['category', $category]
        ])->firstOrFail();

        $validated = $request->validate([
            'companycode'        => 'required|string|max:4',
            'category'       => 'required|string|max:50',
            'jumlahapproval'     => 'required|integer',
            'idjabatanapproval1' => 'nullable|integer',
            'idjabatanapproval2' => 'nullable|integer',
            'idjabatanapproval3' => 'nullable|integer',
        ]);

        if ($request->companycode !== $approval->companycode || $request->category !== $approval->category ) {
            $exists = Approval::where('companycode', $request->companycode)->where('category', $request->category)->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'category' => 'Duplicate Entry, Approval already exists'
                    ]);
            }
        }

        Approval::where([
            ['companycode',  $companycode],
            ['category', $category]
        ])->update([
            'companycode'        => $validated['companycode'],
            'category'           => $validated['category'],
            'jumlahapproval'     => $validated['jumlahapproval'],
            'idjabatanapproval1' => $validated['idjabatanapproval1'],
            'idjabatanapproval2' => $validated['jumlahapproval'] >= 2 ? $validated['idjabatanapproval2'] : NULL,
            'idjabatanapproval3' => $validated['jumlahapproval'] == 3 ? $validated['idjabatanapproval3'] : NULL,
            'updateby'     => Auth::user()->userid,
            'updatedat'    => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil di-update.');
    }

    public function destroy(Request $request, $companycode, $category)
    {
        Approval::where([
            ['companycode',  $companycode],
            ['category', $category]
        ])->delete();

        return redirect()->back()->with('success', 'Data berhasil di-hapus.');
    }
}
