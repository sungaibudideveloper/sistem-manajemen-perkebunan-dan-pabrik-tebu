<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Approval;

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
            ->orderBy('activitycode')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);

        return view('master.approval.index', [
            'approval'   => $approval,
            'title'      => 'Data Approval',
            'navbar'     => 'Master',
            'nav'        => 'Approval',
            'perPage'    => $perPage,
            'search'     => $search,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'companycode'        => 'required|string|max:4',
            'activitycode'       => 'required|string|max:50',
            'jumlahapproval'     => 'required|integer',
            'idjabatanapproval1' => 'nullable|integer',
            'idjabatanapproval2' => 'nullable|integer',
            'idjabatanapproval3' => 'nullable|integer',
        ]);

        $exists = Approval::where('companycode', $request->companycode)
            ->where('activitycode', $request->activitycode)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'activitycode' => 'Duplicate Entry, Approval already exists'
                ]);
        }

        Approval::create([
            'companycode'        => $request->companycode,
            'activitycode'       => $request->activitycode,
            'jumlahapproval'     => $request->jumlahapproval,
            'idjabatanapproval1' => $request->idjabatanapproval1,
            'idjabatanapproval2' => $request->idjabatanapproval2,
            'idjabatanapproval3' => $request->idjabatanapproval3,
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $activitycode)
    {
        $approval = Approval::where([
            ['companycode',  $companycode],
            ['activitycode', $activitycode]
        ])->firstOrFail();

        $validated = $request->validate([
            'companycode'        => 'required|string|max:4',
            'activitycode'       => 'required|string|max:50',
            'jumlahapproval'     => 'required|integer',
            'idjabatanapproval1' => 'nullable|integer',
            'idjabatanapproval2' => 'nullable|integer',
            'idjabatanapproval3' => 'nullable|integer',
        ]);

        if ($request->companycode !== $approval->companycode ||
            $request->activitycode !== $approval->activitycode
        ) {
            $exists = Approval::where('companycode', $request->companycode)
                ->where('activitycode', $request->activitycode)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'activitycode' => 'Duplicate Entry, Approval already exists'
                    ]);
            }
        }

        Approval::where([
            ['companycode',  $companycode],
            ['activitycode', $activitycode]
        ])->update($validated);

        return redirect()->back()->with('success', 'Data berhasil di-update.');
    }

    public function destroy(Request $request, $companycode, $activitycode)
    {
        Approval::where([
            ['companycode',  $companycode],
            ['activitycode', $activitycode]
        ])->delete();

        return redirect()->back()->with('success', 'Data berhasil di-hapus.');
    }
}
