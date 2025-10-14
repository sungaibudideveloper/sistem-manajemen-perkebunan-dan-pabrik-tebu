<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;

use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class CompanyController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'Company',
            'routeName' => route('masterdata.company.index'),
        ]);
    }

    public function index(Request $request)
    {
        $title = "Daftar Company";
        
        // Get user's accessible companies from usercompany table
        $userCompanies = DB::table('usercompany')
            ->where('userid', Auth::user()->userid)
            ->where('isactive', 1)
            ->pluck('companycode')
            ->toArray();

        // Check if user has Admin permission (global access)
        $hasAdminPermission = hasPermission('Admin');

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        if ($hasAdminPermission) {
            // Admin can see all companies they have access to
            $companies = DB::table('company')
                ->whereIn('companycode', $userCompanies)
                ->orderBy('companycode', 'asc')
                ->paginate($perPage);
        } else {
            // Regular user only sees current session company
            $companies = DB::table('company')
                ->where('companycode', '=', session('companycode'))
                ->orderBy('companycode', 'asc')
                ->paginate($perPage);
        }

        foreach ($companies as $index => $item) {
            $item->no = ($companies->currentPage() - 1) * $companies->perPage() + $index + 1;
        }

        return view('master.company.index', compact('companies', 'perPage', 'title'));
    }

    public function handle(Request $request)
    {
        if ($request->has('perPage')) {
            return $this->index($request);
        }

        return $this->store($request);
    }

    protected function requestValidated(): array
    {
        return [
            'companycode' => 'required|max:4',
            'nama' => 'required|max:50',
            'alamat' => 'required',
            'tgl' => 'required',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        
        $exists = DB::table('company')->where('companycode', $request->companycode)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah ada, silahkan coba dengan data yang berbeda.'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            DB::table('company')->insert([
                'companyinventory' => $request->companyinventory,
                'companycode' => $request->companycode,
                'name' => $request->nama,
                'address' => $request->alamat,
                'companyperiod' => $request->companyperiod,
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updatedat' => now()
            ]);

            // Add company access to Admin user
            DB::table('usercompany')
                ->updateOrInsert(
                    ['userid' => 'Admin', 'companycode' => $request->companycode],
                    [
                        'isactive' => 1,
                        'grantedby' => Auth::user()->userid,
                        'createdat' => now(),
                        'updatedat' => now()
                    ]
                );
        });

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'newData' => [
                'no' => 'NEW!',
                'companycode' => $request->companycode,
                'name' => $request->nama,
                'address' => $request->alamat,
                'companyperiod' => $request->tgl,
                'companyinventory' => $request->companyinventory,
            ]
        ]);
    }

    public function update(Request $request, $companycode)
    {
        DB::transaction(function () use ($request, $companycode) {
            $request->validate($this->requestValidated());
            DB::table('company')->where('companycode', $companycode)->update([
                'companycode' => $request->companycode,
                'name' => $request->nama,
                'address' => $request->alamat,
                'companyperiod' => $request->tgl,
                'companyinventory' => $request->companyinventory,
                'updatedat' => now()
            ]);
        });

        return redirect()->route('masterdata.company.index')
            ->with('success1', 'Data updated successfully.');
    }

    // public function destroy($companycode)
    // {
    //     // Check permission
    //     if (!$this->hasPermission('Hapus Company')) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Tidak memiliki akses untuk menghapus company.'
    //         ], 403);
    //     }
    //
    //     DB::transaction(function () use ($companycode) {
    //         $company = DB::table('company')->where('companycode', $companycode)->first();
    //         if (!$company) {
    //             throw new \Exception('Company not found');
    //         }
    //
    //         DB::table('company')->where('companycode', $companycode)->delete();
    //
    //         // Remove company access from all users
    //         DB::table('usercompany')->where('companycode', $companycode)->delete();
    //     });
    //
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Data berhasil dihapus',
    //     ]);
    // }


}