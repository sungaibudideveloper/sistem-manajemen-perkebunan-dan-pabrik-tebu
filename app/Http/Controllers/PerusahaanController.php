<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class PerusahaanController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'Company',
            'routeName' => route('master.perusahaan.index'),
        ]);
    }

    public function index(Request $request)
    {

        $title = "Daftar Company";
        $comp = explode(',', Auth::user()->userComp->kd_comp);
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
            $perusahaan = DB::table('perusahaan')->where(function ($query) use ($comp) {
                foreach ($comp as $company) {
                    $query->orWhereRaw('FIND_IN_SET(?, kd_comp)', [$company]);
                }
            })->distinct()->orderBy('kd_comp', 'asc')->paginate($perPage);
        } else {
            $perusahaan = DB::table('perusahaan')->where('kd_comp', '=', session('dropdown_value'))
                ->orderBy('kd_comp', 'asc')->paginate($perPage);
        }

        foreach ($perusahaan as $index => $item) {
            $item->no = ($perusahaan->currentPage() - 1) * $perusahaan->perPage() + $index + 1;
        }
        return view('master.perusahaan.index', compact('perusahaan', 'perPage', 'title'));
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
            'kd_comp' => 'required|max:4',
            'nama' => 'required|max:50',
            'alamat' => 'required',
            'tgl' => 'required',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('perusahaan')->where('kd_comp', $request->kd_comp)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah ada, silahkan coba dengan data yang berbeda.'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            DB::table('perusahaan')->insert([
                'kd_comp' => $request->kd_comp,
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'tgl' => $request->tgl,
                'user_input' => Auth::user()->usernm,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('usercomp')
                ->where('usernm', 'Admin')
                ->update([
                    'kd_comp' => DB::raw("CONCAT(COALESCE(kd_comp, ''), CASE WHEN kd_comp = '' OR kd_comp IS NULL THEN '' ELSE ',' END, '{$request->kd_comp}')")
                ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'newData' => [
                'no' => 'NEW!',
                'kd_comp' => $request->kd_comp,
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'tgl' => $request->tgl,
            ]
        ]);
    }

    public function update(Request $request, $kd_comp)
    {
        DB::transaction(function () use ($request, $kd_comp) {
            $request->validate($this->requestValidated());
            DB::table('perusahaan')->where('kd_comp',$kd_comp)->update([
                'kd_comp' => $request->kd_comp,
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'tgl' => $request->tgl,
                'updated_at' => now()
            ]);
        });
        return redirect()->route('master.perusahaan.index')
            ->with('success1', 'Data updated successfully.');
    }

    // public function destroy($kd_comp)
    // {
    //     DB::transaction(function () use ($kd_comp) {
    //         $perusahaan = DB::table('perusahaan')->findOrFail($kd_comp);
    //         $perusahaan->delete();
    //         $users = DB::table('usercomp')->where('kd_comp', 'LIKE', "%{$kd_comp}%")->get();

    //         foreach ($users as $user) {
    //             $kd_comp_list = explode(',', $user->kd_comp);
    //             $new_kd_comp_list = array_filter($kd_comp_list, function ($item) use ($kd_comp) {
    //                 return trim($item) !== $kd_comp;
    //             });

    //             $new_kd_comp = implode(',', $new_kd_comp_list);

    //             DB::table('usercomp')->where('usernm', $user->usernm)->update(['kd_comp' => $new_kd_comp]);
    //         }
    //     });
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Data berhasil dihapus',
    //     ]);
    // }
}
