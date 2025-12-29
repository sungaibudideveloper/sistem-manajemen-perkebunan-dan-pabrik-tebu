<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Approval;
use App\Models\MasterData\Jabatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $companycode = Session::get('companycode');

        $query = Approval::with([
            'jabatanApproval1',
            'jabatanApproval2',
            'jabatanApproval3',
        ])->where('companycode', $companycode);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                  ->orWhere('activitygroup', 'like', "%{$search}%");
            });
        }

        $approval = $query
            ->orderBy('activitygroup')
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
        $companycode = Session::get('companycode');

        $request->validate([
            'category'           => 'required|string|max:150',
            'activitygroup'      => 'nullable|string|max:5',
            'jumlahapproval'     => 'required|integer|min:1|max:3',
            'idjabatanapproval1' => 'required|integer|exists:jabatan,idjabatan',
            'idjabatanapproval2' => 'nullable|integer|exists:jabatan,idjabatan',
            'idjabatanapproval3' => 'nullable|integer|exists:jabatan,idjabatan',
        ], [
            'jumlahapproval.max' => 'Jumlah approval maksimal 3',
            'idjabatanapproval1.required' => 'Jabatan approval 1 wajib diisi',
            'idjabatanapproval1.exists' => 'Jabatan approval 1 tidak valid',
            'idjabatanapproval2.exists' => 'Jabatan approval 2 tidak valid',
            'idjabatanapproval3.exists' => 'Jabatan approval 3 tidak valid',
        ]);

        // Validasi jumlah approval vs jabatan yang diisi
        if ($request->jumlahapproval >= 2 && !$request->idjabatanapproval2) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['idjabatanapproval2' => 'Jabatan approval 2 wajib diisi untuk jumlah approval >= 2']);
        }

        if ($request->jumlahapproval == 3 && !$request->idjabatanapproval3) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['idjabatanapproval3' => 'Jabatan approval 3 wajib diisi untuk jumlah approval = 3']);
        }

        // Cek duplicate category
        $exists = Approval::where('companycode', $companycode)
            ->where('category', $request->category)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category' => 'Duplicate Entry, Category already exists']);
        }

        // Cek duplicate activitygroup jika diisi
        if ($request->activitygroup) {
            $existsGroup = Approval::where('companycode', $companycode)
                ->where('activitygroup', $request->activitygroup)
                ->exists();

            if ($existsGroup) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['activitygroup' => 'Duplicate Entry, Activity Group already exists']);
            }
        }

        Approval::create([
            'companycode'        => $companycode,
            'category'           => $request->category,
            'activitygroup'      => $request->activitygroup,
            'jumlahapproval'     => $request->jumlahapproval,
            'idjabatanapproval1' => $request->idjabatanapproval1,
            'idjabatanapproval2' => $request->jumlahapproval >= 2 ? $request->idjabatanapproval2 : NULL,
            'idjabatanapproval3' => $request->jumlahapproval == 3 ? $request->idjabatanapproval3 : NULL,
            'inputby'            => Auth::user()->userid,
            'createdat'          => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $category)
    {
        $sessionCompanycode = Session::get('companycode');

        // Security check
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        $approval = Approval::where([
            ['companycode', $companycode],
            ['category', $category]
        ])->firstOrFail();

        $validated = $request->validate([
            'category'           => 'required|string|max:150',
            'activitygroup'      => 'nullable|string|max:5',
            'jumlahapproval'     => 'required|integer|min:1|max:3',
            'idjabatanapproval1' => 'required|integer|exists:jabatan,idjabatan',
            'idjabatanapproval2' => 'nullable|integer|exists:jabatan,idjabatan',
            'idjabatanapproval3' => 'nullable|integer|exists:jabatan,idjabatan',
        ], [
            'jumlahapproval.max' => 'Jumlah approval maksimal 3',
            'idjabatanapproval1.required' => 'Jabatan approval 1 wajib diisi',
            'idjabatanapproval1.exists' => 'Jabatan approval 1 tidak valid',
            'idjabatanapproval2.exists' => 'Jabatan approval 2 tidak valid',
            'idjabatanapproval3.exists' => 'Jabatan approval 3 tidak valid',
        ]);

        // Validasi jumlah approval vs jabatan yang diisi
        if ($validated['jumlahapproval'] >= 2 && !$validated['idjabatanapproval2']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['idjabatanapproval2' => 'Jabatan approval 2 wajib diisi untuk jumlah approval >= 2']);
        }

        if ($validated['jumlahapproval'] == 3 && !$validated['idjabatanapproval3']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['idjabatanapproval3' => 'Jabatan approval 3 wajib diisi untuk jumlah approval = 3']);
        }

        // Cek duplicate category jika berubah
        if ($request->category !== $approval->category) {
            $exists = Approval::where('companycode', $companycode)
                ->where('category', $request->category)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['category' => 'Duplicate Entry, Category already exists']);
            }
        }

        // Cek duplicate activitygroup jika berubah dan diisi
        if ($request->activitygroup && $request->activitygroup !== $approval->activitygroup) {
            $existsGroup = Approval::where('companycode', $companycode)
                ->where('activitygroup', $request->activitygroup)
                ->exists();

            if ($existsGroup) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['activitygroup' => 'Duplicate Entry, Activity Group already exists']);
            }
        }

        Approval::where([
            ['companycode', $companycode],
            ['category', $category]
        ])->update([
            'companycode'        => $companycode,
            'category'           => $validated['category'],
            'activitygroup'      => $validated['activitygroup'],
            'jumlahapproval'     => $validated['jumlahapproval'],
            'idjabatanapproval1' => $validated['idjabatanapproval1'],
            'idjabatanapproval2' => $validated['jumlahapproval'] >= 2 ? $validated['idjabatanapproval2'] : NULL,
            'idjabatanapproval3' => $validated['jumlahapproval'] == 3 ? $validated['idjabatanapproval3'] : NULL,
            'updateby'           => Auth::user()->userid,
            'updatedat'          => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil di-update.');
    }

    public function destroy(Request $request, $companycode, $category)
    {
        $sessionCompanycode = Session::get('companycode');

        // Security check
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        Approval::where([
            ['companycode', $companycode],
            ['category', $category]
        ])->delete();

        return redirect()->back()->with('success', 'Data berhasil di-hapus.');
    }
}