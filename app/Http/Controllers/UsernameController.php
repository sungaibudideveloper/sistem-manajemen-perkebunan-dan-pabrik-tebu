<?php

namespace App\Http\Controllers;

use App\Models\Usercomp;
use App\Models\User;
use App\Models\company;
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
        $username = DB::table('user')
            ->join('usercompany', 'user.userid', '=', 'usercompany.userid')
            ->select('usercompany.*', 'user.*')
            ->where('user.userid', '!=', 'Admin')
            ->orderBy('user.userid', 'asc')
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
        $company = DB::table('company')->get();
        return view('master.username.create', compact('title', 'company'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usernm' => 'required|unique:user,userid',
            'name' => 'required',
            'password' => 'required',
            'permissions' => 'nullable|array',
            'companycode' => 'required|array',
        ]);

        $exists = DB::table('usercompany')->where('userid', $request->usernm)
            ->where('companycode', $request->companycode)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'duplicate' => 'Data sudah ada, silahkan coba dengan data yang berbeda.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated) {
            $companycode = implode(',', $validated['companycode']);
            DB::table('user')->insert([
                'userid' => strtoupper($validated['usernm']),
                'name' => strtoupper($validated['name']),
                'password' => bcrypt($validated['password']),
                'permissions' => $validated['permissions'] ? json_encode($validated['permissions']) : null,
                'createdat' => now(),
                'updatedat' => now(),
            ]);
            DB::table('usercompany')->insert([
                'userid' => $validated['usernm'],
                'companycode' => $companycode,
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updatedat' => now(),
            ]);
        });
        return redirect()->back()
            ->with('success1', 'Data created successfully.');
    }

    public function edit($usernm, $companycode)
    {
        $title = 'Edit Data';
        $user = DB::table('user')->where('userid', $usernm)->first();
        $usercompany = DB::table('usercompany')->where('userid', $usernm)
            ->where('companycode', $companycode)
            ->first();
        $company = DB::table('company')->get();
        return view('master.username.edit', compact('user', 'usercompany', 'company', 'title'));
    }

    public function update(Request $request, $usernm, $companycode)
    {
        $validated = $request->validate([
            'usernm' => 'required',
            'name' => 'required',
            'password' => 'required',
            'companycode' => 'required|array',
        ]);

        DB::transaction(function () use ($usernm, $companycode, $validated) {
            DB::table('user')
                ->where('userid', $usernm)
                ->update([
                    'userid' => $validated['usernm'],
                    'name' => strtoupper($validated['name']),
                    'password' => bcrypt($validated['password']),
                ]);;
            DB::table('usercompany')->where('userid', $usernm)
                ->where('companycode', $companycode)
                ->update([
                    'userid' => strtoupper($validated['usernm']),
                    'companycode' => implode(',', array_filter($validated['companycode'])),
                    'inputby' => Auth::user()->userid,
                ]);
        });
        return redirect()->route('master.username.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function access($usernm)
    {
        $title = 'Set Hak Akses';
        $user = User::findOrFail($usernm);

        return view('master.username.access', compact('user', 'title'));
    }

    public function setaccess(Request $request, $usernm)
    {
        $validated = $request->validate([
            'userid' => 'required',
            'permissions' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated, $usernm) {
            DB::table('user')
            ->where('userid', $usernm)
            ->update([
                'userid' => $validated['userid'],
                'permissions' => $validated['permissions'] ?? null,
            ]);
        });

        return redirect()->route('master.username.index')
            ->with('success', 'Data updated successfully.');
    }

    public function destroy($usernm, $companycode)
    {
        DB::transaction(function () use ($usernm, $companycode) {
            DB::table('user')->where('userid', $usernm)->delete();
            DB::table('usercompany')->where('userid', $usernm)->where('companycode', $companycode)->delete();
        });
        return redirect()->route('master.username.index')
            ->with('success', 'Data deleted successfully.');
    }
}
