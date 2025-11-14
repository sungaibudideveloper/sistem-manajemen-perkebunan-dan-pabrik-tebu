<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\Herbisida;
use App\Models\Herbisidagroup;
use App\Models\Herbisidadosage;
use Illuminate\Support\Facades\DB;

class HerbisidaGroupController extends Controller
{
    
    public function home(Request $request)
{
    $companycode = Session::get('companycode');
    $search = $request->get('search');
    $perPage = (int)$request->get('perPage', 10);
    
    // Get next herbisidagroupid
    $nextId = (Herbisidagroup::max('herbisidagroupid') ?? 0) + 1;
    
    // Get activities
    $activities = DB::table('activity')
        ->select('activitycode', 'activityname')
        ->orderBy('activitycode')
        ->get();
    
    $herbisidaItems = Herbisida::where('companycode', $companycode)
        ->select('itemcode', 'itemname', 'measure')
        ->orderBy('itemname')
        ->get();

    // Get group IDs yang punya data di herbisidadosage (untuk filter search)
    $query = Herbisidagroup::query()
        ->whereExists(function($q) use ($companycode) {
            $q->select(DB::raw(1))
              ->from('herbisidadosage')
              ->whereColumn('herbisidadosage.herbisidagroupid', 'herbisidagroup.herbisidagroupid')
              ->where('herbisidadosage.companycode', $companycode);
        });
    
    // Apply search filter
    if ($search) {
        $query->where(function($q) use ($search, $companycode) {
            $q->where('herbisidagroupname', 'like', "%{$search}%")
              ->orWhere('activitycode', 'like', "%{$search}%")
              ->orWhereExists(function($subq) use ($search, $companycode) {
                  $subq->select(DB::raw(1))
                       ->from('herbisidadosage as b')
                       ->leftJoin('herbisida as c', function($join) use ($companycode) {
                           $join->on('b.itemcode', '=', 'c.itemcode')
                                ->where('c.companycode', '=', $companycode);
                       })
                       ->whereColumn('b.herbisidagroupid', 'herbisidagroup.herbisidagroupid')
                       ->where('b.companycode', $companycode)
                       ->where(function($w) use ($search) {
                           $w->where('b.itemcode', 'like', "%{$search}%")
                             ->orWhere('c.itemname', 'like', "%{$search}%");
                       });
              });
        });
    }
    
    // Paginate group IDs
    $groupIds = $query->orderBy('herbisidagroupid')->paginate($perPage);
    
    // Get full data for these group IDs
    $grouping = HerbisidaGroup::query()
        ->leftJoin('herbisidadosage as b', 'herbisidagroup.herbisidagroupid', '=', 'b.herbisidagroupid')
        ->leftJoin('herbisida as c', function($join) use ($companycode) {
            $join->on('b.itemcode', '=', 'c.itemcode')
                 ->where('c.companycode', '=', $companycode);
        })
        ->select('herbisidagroup.*', 'b.itemcode', 'b.dosageperha', 'c.itemname')
        ->where('b.companycode', $companycode)
        ->whereIn('herbisidagroup.herbisidagroupid', $groupIds->pluck('herbisidagroupid'))
        ->orderBy('herbisidagroup.herbisidagroupid')
        ->orderBy('b.itemcode')
        ->get();
    
    // Add pagination info to collection
    $grouping = new \Illuminate\Pagination\LengthAwarePaginator(
        $grouping,
        $groupIds->total(),
        $groupIds->perPage(),
        $groupIds->currentPage(),
        ['path' => $request->url(), 'query' => $request->query()]
    );

    return view('master.herbisidagroup.index', [
        'title'           => 'Data Herbisida Group',
        'navbar'          => 'Master',
        'nav'             => 'Herbisida Group',
        'grouping'        => $grouping,
        'herbisidaItems'  => $herbisidaItems,
        'activities'      => $activities,
        'nextId'          => $nextId
    ]);
}

public function insert(Request $request)
{
    $companycode = Session::get('companycode');  // ← AMBIL COMPANYCODE
    
    // Cek apakah herbisidagroupid sudah ada
    $existingId = Herbisidagroup::where('herbisidagroupid', $request->herbisidagroupid)->first();
    if ($existingId) {
        return back()->withInput()->with('error', 'Group ID sudah ada!');
    }

    $request->validate([
        'herbisidagroupid' => 'required|integer',
        'herbisidagroupname' => 'required|max:100',
        'activitycode' => 'required|exists:activity,activitycode',
        'description' => 'nullable',
        'items' => 'required|array|min:1',
        'items.*.itemcode' => 'required|exists:herbisida,itemcode',
        'items.*.dosageperha' => 'required|numeric|min:0'
    ]);

    // Check for duplicate item codes in the submitted items
    $itemCodes = array_column($request->items, 'itemcode');
    if (count($itemCodes) !== count(array_unique($itemCodes))) {
        return back()->withInput()->with('error', 'Kode Item Duplikat!');
    }

    DB::beginTransaction();
    try {
        // Create herbisida group
        $group = Herbisidagroup::create([
            'herbisidagroupid' => $request->herbisidagroupid,
            'herbisidagroupname' => $request->herbisidagroupname,
            'activitycode' => $request->activitycode,
            'description' => $request->description
        ]);
        
        // Insert herbisida dosage items
        foreach ($request->items as $item) {
            Herbisidadosage::create([
                'herbisidagroupid' => $group->herbisidagroupid,
                'itemcode' => $item['itemcode'],
                'dosageperha' => $item['dosageperha'],
                'companycode' => $companycode  // ← TAMBAH INI
            ]);
        }
        
        DB::commit();
        return redirect()->route('masterdata.herbisida-group.index')->with('success', 'Grup Berhasil Dibuat!');
        
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal Buat Grup: ' . $e->getMessage());
    }
}

public function edit(Request $request, $id)
{
    $companycode = Session::get('companycode');  // ← AMBIL COMPANYCODE
    $group = Herbisidagroup::findOrFail($id);

    $request->validate([
        'herbisidagroupname' => 'required|max:100',
        'activitycode' => 'required|exists:activity,activitycode',
        'description' => 'nullable',
        'items' => 'required|array|min:1',
        'items.*.itemcode' => 'required|exists:herbisida,itemcode',
        'items.*.dosageperha' => 'required|numeric|min:0'
    ]);

    $isUsed = DB::table('rkhlst')
    ->where('activitycode', $group->activitycode)
    ->where('herbisidagroupid', $group->herbisidagroupid)
    ->exists();

    if ($isUsed) {
    return back()->with('error', 'Gagal Edit! Group ini sudah digunakan di RKH dan tidak bisa diubah!');
    }

    // Check for duplicate item codes in the submitted items
    $itemCodes = array_column($request->items, 'itemcode');
    if (count($itemCodes) !== count(array_unique($itemCodes))) {
        return back()->withInput()->with('error', 'Kode Item Duplikat!');
    }

    DB::beginTransaction();
    try {
        // Update group
        $group->update([
            'herbisidagroupname' => $request->herbisidagroupname,
            'activitycode' => $request->activitycode,
            'description' => $request->description
        ]);
        
        // Delete old dosages
        Herbisidadosage::where('herbisidagroupid', $id)->delete();
        
        // Insert new dosages
        foreach ($request->items as $item) {
            Herbisidadosage::create([
                'herbisidagroupid' => $id,
                'itemcode' => $item['itemcode'],
                'dosageperha' => $item['dosageperha'],
                'companycode' => $companycode  // ← TAMBAH INI
            ]);
        }
        
        DB::commit();
        return redirect()->route('masterdata.herbisida-group.index')->with('success', 'Edit Sukses!');
        
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal Update Group: ' . $e->getMessage());
    }
}

public function delete($id)
{
    $group = Herbisidagroup::findOrFail($id);
    
    // Check if activitycode is being used in rkhlst
    $isUsed = DB::table('rkhlst')
                ->where('activitycode', $group->activitycode)
                ->where('herbisidagroupid', $group->herbisidagroupid)
                ->exists();
    
    if ($isUsed) {
        return back()->with('error', 'Gagal Hapus! Sudah ada di RKH!');
    }
    
    DB::beginTransaction();
    try {
        // Delete dosages first
        Herbisidadosage::where('herbisidagroupid', $id)->delete();
        
        // Delete group
        $group->delete();
        
        DB::commit();
        return redirect()->route('masterdata.herbisida-group.index')->with('success', 'Group Sukses Dihapus!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal Hapus Group: ' . $e->getMessage());
    }
}

    
}