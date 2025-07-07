<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\UserCompany;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Menu;
use App\Models\Submenu;
use App\Models\Subsubmenu;
use function Laravel\Prompts\select;

class UsernameController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'Kelola User',
            'routeName' => route('masterdata.username.index')
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
            //->where('user.userid', '!=', 'Admin')
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

        // Get data untuk form
        $company = Company::orderBy('companycode')->get();
        $menu = Menu::orderBy('menuid')->get()->unique('slug')->values();
        $submenu = Submenu::orderBy('submenuid')->get();
        $subsubmenu = Subsubmenu::orderBy('name')->get();

        return view('master.username.create', [
            'title' => $title,
            'company' => $company,
            'menu' => $menu,
            'submenu' => $submenu,
            'subsubmenu' => $subsubmenu,
            'username' => new User(), // empty model untuk old() values
        ]);
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

        // Cek duplicate untuk setiap company
        foreach ($validated['companycode'] as $company) {
            $exists = DB::table('usercompany')
                ->where('userid', $request->usernm)
                ->where('companycode', $company)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'duplicate' => "User {$request->usernm} sudah ada untuk company {$company}",
                ])->withInput();
            }
        }

        DB::transaction(function () use ($validated) {
            // Insert ke tabel user
            DB::table('user')->insert([
                'userid' => strtoupper($validated['usernm']),
                'name' => strtoupper($validated['name']),
                'password' => bcrypt($validated['password']),
                'companycode' => implode(',', $validated['companycode']), // Gabungan untuk display
                'permissions' => $validated['permissions'] ? json_encode($validated['permissions']) : null,
                'createdat' => now(),
                'updatedat' => now(),
                'isactive' => 1,
            ]);

            // Insert ke usercompany - satu row per company
            foreach ($validated['companycode'] as $company) {
                DB::table('usercompany')->insert([
                    'userid' => strtoupper($validated['usernm']),
                    'companycode' => $company,
                    'inputby' => Auth::user()->userid,
                    'createdat' => now(),
                ]);
            }
        });

        return redirect()->route('masterdata.username.index')
            ->with('success', 'User berhasil ditambahkan.');
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
        return redirect()->route('masterdata.username.index')
            ->with('success1', 'Data updated successfully.');
    }

    public function access($usernm)
    {
        $title = 'Set Hak Akses';
        $userdata = User::where('userid', $usernm)->firstOrFail();
        // Filter unique by slug directly
        $menu = Menu::orderBy('menuid')->get()->unique('slug')->values();
        $submenu = Submenu::orderBy('submenuid')->get();
        $subsubmenu = Subsubmenu::orderBy('name')->get();

        return view('master.username.access', [
            'userdata' => $userdata,
            'title' => $title,
            'menu' => $menu,
            'submenu' => $submenu,
            'subsubmenu' => $subsubmenu,
        ]);
    }

    public function setaccess(Request $request, $usernm)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
        ]);
        DB::transaction(function () use ($validated, $usernm) {

            DB::table('user')
                ->where('userid', $usernm)
                ->update([
                    'permissions' => $validated['permissions'] ? json_encode($validated['permissions']) : null,
                    'updatedat' => now(),
                ]);
        });

        // Jika ingin tetap di halaman access:
        return redirect()->route('masterdata.username.index', $usernm)
            ->with('success', 'Data updated successfully.');

        // Kalau mau ke index:
        // return redirect()->route('masterdata.username.index')->with('success', 'Data updated successfully.');
    }



    public function destroy($usernm, $companycode)
    {
        DB::transaction(function () use ($usernm, $companycode) {
            DB::table('user')->where('userid', $usernm)->delete();
            DB::table('usercompany')->where('userid', $usernm)->where('companycode', $companycode)->delete();
        });
        return redirect()->route('masterdata.username.index')
            ->with('success', 'Data deleted successfully.');
    }
}
