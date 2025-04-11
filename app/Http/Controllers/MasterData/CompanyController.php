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
            'routeName' => route('master.company.index'),
        ]);
    }

    public function index(Request $request)
    {

        $title = "Daftar Company";
        $comp = explode(',', Auth::user()->userComp->companycode);
        $permissions = json_decode(Auth::user()->permissions, true);
        $isAdmin = in_array('Admin', $permissions);

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        if ($isAdmin) {
            $company = DB::table('company')->where(function ($query) use ($comp) {
                foreach ($comp as $company) {
                    $query->orWhereRaw('FIND_IN_SET(?, companycode)', [$company]);
                }
            })->distinct()->orderBy('companycode', 'asc')->paginate($perPage);
        } else {
            $company = DB::table('company')->where('companycode', '=', session('companycode'))
                ->orderBy('companycode', 'asc')->paginate($perPage);
        }

        foreach ($company as $index => $item) {
            $item->no = ($company->currentPage() - 1) * $company->perPage() + $index + 1;
        }
        return view('master.company.index', compact('company', 'perPage', 'title'));
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
                'companycode' => $request->companycode,
                'name' => $request->nama,
                'address' => $request->alamat,
                'companyperiod' => $request->companyperiod,
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updatedat' => now()
            ]);

            DB::table('usercompany')
                ->where('userid', 'Admin')
                ->update([
                    'companycode' => DB::raw("CONCAT(COALESCE(companycode, ''), CASE WHEN companycode = '' OR companycode IS NULL THEN '' ELSE ',' END, '{$request->companycode}')")
                ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'newData' => [
                'no' => 'NEW!',
                'companycode' => $request->companycode,
                'name' => $request->nama,
                'address' => $request->alamat,
                'companuperiod' => $request->tgl,
            ]
        ]);
    }

    public function update(Request $request, $companycode)
    {
        DB::transaction(function () use ($request, $companycode) {
            $request->validate($this->requestValidated());
            DB::table('company')->where('companycode',$companycode)->update([
                'companycode' => $request->companycode,
                'name' => $request->nama,
                'address' => $request->alamat,
                'companyperiod' => $request->tgl,
                'updatedat' => now()
            ]);
        });
        return redirect()->route('master.company.index')
            ->with('success1', 'Data updated successfully.');
    }

    // public function destroy($companycode)
    // {
    //     DB::transaction(function () use ($companycode) {
    //         $company = DB::table('company')->findOrFail($companycode);
    //         $company->delete();
    //         $users = DB::table('usercomp')->where('companycode', 'LIKE', "%{$companycode}%")->get();

    //         foreach ($users as $user) {
    //             $companycode_list = explode(',', $user->companycode);
    //             $new_companycode_list = array_filter($companycode_list, function ($item) use ($companycode) {
    //                 return trim($item) !== $companycode;
    //             });

    //             $new_companycode = implode(',', $new_companycode_list);

    //             DB::table('usercomp')->where('usernm', $user->usernm)->update(['companycode' => $new_companycode]);
    //         }
    //     });
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Data berhasil dihapus',
    //     ]);
    // }
}
