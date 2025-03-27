<?php

namespace App\Http\Controllers;

use App\Models\Usercomp;
use App\Models\Username;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

use function Laravel\Prompts\select;

class UsernameController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'Kelola User',
            'routeName' => route('master.username.index')
        ]);
    }
    public function index(Request $request)
    {

        $title = "Daftar User";

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        $username = DB::table('username')
            ->join('usercomp', 'username.usernm', '=', 'usercomp.usernm')
            ->select('usercomp.*', 'username.*')
            ->where('username.usernm', '!=', 'Admin')
            ->orderBy('username.usernm', 'asc')
            ->paginate($perPage);

        foreach ($username as $index => $item) {
            $item->no = ($username->currentPage() - 1) * $username->perPage() + $index + 1;
        }
        return view('master.username.index', compact('username', 'perPage', 'title'));
    }

    public function handle(Request $request)
    {
        if ($request->has('perPage')) {
            return $this->index($request);
        }

        return $this->store($request);
    }

    public function create()
    {
        $title = "Create Data";
        $company = DB::table('perusahaan')->get();
        return view('master.username.create', compact('title', 'company'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usernm' => 'required|unique:username,usernm',
            'name' => 'required',
            'password' => 'required',
            'permissions' => 'nullable|array',
            'kd_comp' => 'required|array',
        ]);

        $exists = DB::table('usercomp')->where('usernm', $request->usernm)
            ->where('kd_comp', $request->kd_comp)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'duplicate' => 'Data sudah ada, silahkan coba dengan data yang berbeda.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated) {
            $kd_comp = implode(',', $validated['kd_comp']);
            DB::table('username')->insert([
                'usernm' => $validated['usernm'],
                'name' => $validated['name'],
                'password' => bcrypt($validated['password']),
                'permissions' => $validated['permissions'] ? json_encode($validated['permissions']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('usercomp')->insert([
                'usernm' => $validated['usernm'],
                'kd_comp' => $kd_comp,
                'user_input' => Auth::user()->usernm,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
        return redirect()->back()
            ->with('success1', 'Data created successfully.');
    }

    public function edit($usernm, $kd_comp)
    {
        $title = 'Edit Data';
        $username = DB::table('username')->where('usernm', $usernm)->first();
        $usercomp = DB::table('usercomp')->where('usernm', $usernm)
            ->where('kd_comp', $kd_comp)
            ->first();
        $company = DB::table('perusahaan')->get();
        return view('master.username.edit', compact('username', 'usercomp', 'company', 'title'));
    }

    public function update(Request $request, $usernm, $kd_comp)
    {
        $validated = $request->validate([
            'usernm' => 'required',
            'name' => 'required',
            'password' => 'required',
            'kd_comp' => 'required|array',
        ]);

        DB::transaction(function () use ($usernm, $kd_comp, $validated) {
            DB::table('username')
                ->where('usernm', $usernm)
                ->update([
                    'usernm' => $validated['usernm'],
                    'name' => $validated['name'],
                    'password' => bcrypt($validated['password']),
                ]);;
            DB::table('usercomp')->where('usernm', $usernm)
                ->where('kd_comp', $kd_comp)
                ->update([
                    'usernm' => $validated['usernm'],
                    'kd_comp' => implode(',', array_filter($validated['kd_comp'])),
                    'user_input' => Auth::user()->usernm,
                ]);
        });
        return redirect()->route('master.username.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function access($usernm)
    {
        $title = 'Set Hak Akses';
        $username = Username::findOrFail($usernm);

        return view('master.username.access', compact('username', 'title'));
    }

    public function setaccess(Request $request, $usernm)
    {
        $validated = $request->validate([
            'usernm' => 'required',
            'permissions' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated, $usernm) {
            DB::table('username')
            ->where('usernm', $usernm)
            ->update([
                'usernm' => $validated['usernm'],
                'permissions' => $validated['permissions'] ?? null,
            ]);
        });

        return redirect()->route('master.username.index')
            ->with('success', 'Data updated successfully.');
    }

    public function destroy($usernm, $kd_comp)
    {
        DB::transaction(function () use ($usernm, $kd_comp) {
            DB::table('username')->where('usernm', $usernm)->delete();
            DB::table('usercomp')->where('usernm', $usernm)->where('kd_comp', $kd_comp)->delete();
        });
        return redirect()->route('master.username.index')
            ->with('success', 'Data deleted successfully.');
    }
}
