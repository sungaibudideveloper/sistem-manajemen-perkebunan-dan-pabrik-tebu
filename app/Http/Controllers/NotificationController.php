<?php

namespace App\Http\Controllers;

use App\Models\company;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class NotificationController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Notification',
            'routeName' => route('notifications.index'),
        ]);
    }

    protected function requestValidated(): array
    {
        return [
            'companycode' => 'required',
            'title' => 'required',
            'body' => 'required',
        ];
    }

    public function index()
    {
        $title = 'Notifications';
        $comp = explode(',', Auth::user()->userComp->companycode);
        $dropdownValue = session('dropdown_value');

        $permissions = json_decode(Auth::user()->permissions, true);
        $isKepalaKebun = in_array('Kepala Kebun', $permissions);
        $isAdmin = in_array('Admin', $permissions);

        $notifQuery = DB::table('notification')
            ->join('company', function ($join) {
                $join->whereRaw('FIND_IN_SET(company.companycode, notification.companycode)');
            });

        if ($isAdmin) {
            $notifQuery->where(function ($query) use ($comp) {
                foreach ($comp as $company) {
                    $query->orWhereRaw('FIND_IN_SET(?, notification.companycode)', [$company]);
                }
            })->distinct();
        } else {
            $notifQuery->whereRaw('FIND_IN_SET(?, notification.companycode)', [session('dropdown_value')])->distinct();
        }

        if (!$isKepalaKebun) {
            $notifQuery->where('notification.inputby', '!=', 'Automatic by System');
        }

        $notif = $notifQuery->whereBetween('notification.createdat', [DB::raw('company.tgl'), now()])
            ->select('notification.*')
            ->orderBy('createdat', 'desc')
            ->get()
            ->map(function ($item) {
                $item->createdat = Carbon::parse($item->createdat);
                return $item;
            });

        $notifCount = $notif->count();

        return view('notifications.index', compact('title', 'notif', 'notifCount'));
    }

    public function create()
    {
        $title = "Create Data";
        $notification = new Notification();
        $company = company::all();
        $method = 'POST';
        $buttonSubmit = 'Create';
        $url = route('notifications.store');
        return view('notifications.form', compact('title', 'company', 'notification', 'method', 'url', 'buttonSubmit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());

        DB::transaction(function () use ($validated) {
            $companycode = implode(',', $validated['companycode']);
            $existingIds = Notification::pluck('id')->toArray();
            sort($existingIds);

            $nextId = 1;
            foreach ($existingIds as $id) {
                if ($id != $nextId) {
                    break;
                }
                $nextId++;
            }

            Notification::create([
                'id' => $nextId,
                'companycode' => $companycode,
                'title' => $validated['title'],
                'body' => $validated['body'],
                'inputby' => Auth::user()->usernm,
            ]);
        });

        return redirect()->back()->with('success1', 'Notifikasi Telah ditambahkan.');
    }

    public function edit($id)
    {
        $title = 'Edit Data';
        $notification = Notification::findOrFail($id);
        $company = company::all();
        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('notifications.update', $notification);
        return view('notifications.form', compact('notification', 'company', 'title', 'method', 'url', 'buttonSubmit'));
    }

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $notifications = Notification::findOrFail($id);
            $notifications->update($request->validate($this->requestValidated()));
        });
        return redirect()->route('notifications.index');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $notifications = Notification::findOrFail($id);
            $notifications->delete();
        });
        return redirect()->route('notifications.index');
    }

    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $currentUser = Auth::user()->usernm;
        $readBy = $notification->readby ? json_decode($notification->readby, true) : [];

        if (!in_array($currentUser, $readBy)) {
            $readBy[] = $currentUser;
            $notification->readby = json_encode($readBy);
            $notification->save();
        }

        return response()->json(['message' => 'Notification marked as read'], 200);
    }

    public function getUnreadCount()
    {
        $currentUser = Auth::user()->usernm;
        $comp = explode(',', Auth::user()->userComp->companycode);
        $permissions = json_decode(Auth::user()->permissions, true);
        $isKepalaKebun = in_array('Kepala Kebun', $permissions);
        $isAdmin = in_array('Admin', $permissions);

        $query = DB::table('notification')
            ->join('company', function ($join) {
                $join->whereRaw('FIND_IN_SET(company.companycode, notification.companycode)');
            });

        if ($isAdmin) {
            $query->where(function ($query) use ($comp, $currentUser) {
                $query->where(function ($query) use ($comp) {
                    foreach ($comp as $kdComp) {
                        $query->orWhereRaw('FIND_IN_SET(?, notification.companycode)', [$kdComp]);
                    }
                })
                    ->where(function ($query) use ($currentUser) {
                        $query->where('notification.readby', '=', '')
                            ->orWhereRaw('NOT JSON_CONTAINS(notification.readby, ?)', [json_encode($currentUser)]);
                    });
            })
                ->distinct();
        } else {
            $query->whereRaw('FIND_IN_SET(?, notification.companycode)', [session('dropdown_value')])
                ->where(function ($query) use ($currentUser) {
                    $query->where('notification.readby', '=', '')
                        ->orWhereRaw('NOT JSON_CONTAINS(notification.readby, ?)', [json_encode($currentUser)]);
                })
                ->distinct();
        }

        if (!$isKepalaKebun) {
            $query->where('notification.inputby', '!=', 'Automatic by System');
        }

        $notif = $query->whereBetween('notification.createdat', [DB::raw('company.tgl'), now()])
            ->select('notification.*')
            ->orderBy('createdat', 'desc')
            ->get()
            ->map(function ($item) {
                $item->createdat = Carbon::parse($item->createdat);
                return $item;
            });

        $unreadCount = $notif->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    public function agronomiNotif()
    {
        DB::transaction(function () {
            $lastCheckedTime = DB::table('notification')
                ->max('createdat');

            if (!$lastCheckedTime) {
                $lastCheckedTime = '2025-01-01 00:00:00';
            }
            $data = DB::table('agro_lst')
                ->select('companycode as comp', 'no_sample as sample', 'nourut as urut', 'per_germinasi', 'per_gulma', 'tanggaltanam')
                ->where('createdat', '>', $lastCheckedTime)
                ->where(function ($query) {
                    $query->where('per_germinasi', '<', 0.9)
                        ->orWhere('per_gulma', '>', 0.25);
                })
                ->get();

            $existingIds = Notification::orderBy('id')->pluck('id')->toArray();
            $nextId = 1;
            foreach ($existingIds as $id) {
                if ($id != $nextId) {
                    break;
                }
                $nextId++;
            }

            foreach ($data as $item) {
                if ($item->tanggaltanam) {
                    $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
                } else {
                    $item->umur_tanam = null;
                }

                if ($item->per_germinasi < 0.9 && ceil($item->umur_tanam) == 1.0) {
                    Notification::create([
                        'id' => $nextId,
                        'companycode' => $item->comp,
                        'title' => 'Agronomi - Persentase Germinasi < 90%',
                        'body' => 'Persentase Germinasi kurang dari 90% untuk nomor sample ' . $item->sample . ', nomor urut ' . $item->urut . ', berumur ' . ceil($item->umur_tanam) . ' bulan.',
                        'inputby' => 'Automatic by System',
                    ]);
                    $nextId++;
                }

                if ($item->per_gulma > 0.25) {
                    Notification::create([
                        'id' => $nextId,
                        'companycode' => $item->comp,
                        'title' => 'Agronomi - Persentase Penutupan Gulma > 25%',
                        'body' => 'Persentase Penutupan Gulma lebih dari 25% untuk nomor sample ' . $item->sample . ', nomor urut ' . $item->urut . '.',
                        'inputby' => 'Automatic by System',
                    ]);
                    $nextId++;
                }
            }
        });
    }

    public function hptNotif()
    {
        DB::transaction(function () {
            $lastCheckedTime = DB::table('notification')
                ->max('createdat');

            if (!$lastCheckedTime) {
                $lastCheckedTime = '2025-01-01 00:00:00';
            }
            $data = DB::table('hpt_lst')
                ->select('companycode as comp', 'no_sample as sample', 'nourut as urut', 'per_pbt', 'tanggaltanam')
                ->where('createdat', '>', $lastCheckedTime)
                ->where(function ($query) {
                    $query->where('per_pbt', '>', 0.03)
                        ->orWhere('per_ppt', '>', 0.03);
                })
                ->get();

            $existingIds = Notification::orderBy('id')->pluck('id')->toArray();
            $nextId = 1;
            foreach ($existingIds as $id) {
                if ($id != $nextId) {
                    break;
                }
                $nextId++;
            }

            foreach ($data as $item) {
                $item->umur_tanam = $item->tanggaltanam ? Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now()) : null;
                $umur_tanam = ceil($item->umur_tanam);

                $notifications = [
                    ['per' => $item->per_pbt, 'threshold' => 0.03, 'min_age' => 1, 'max_age' => 3, 'title' => 'HPT - Persentase PBT > 3%', 'type' => 'penggerek batang tebu'],
                    ['per' => $item->per_ppt, 'threshold' => 0.03, 'min_age' => 1, 'max_age' => 3, 'title' => 'HPT - Persentase PPT > 3%', 'type' => 'penggerek pucuk tebu'],
                    ['per' => $item->per_pbt, 'threshold' => 0.05, 'min_age' => 4, 'max_age' => null, 'title' => 'HPT - Persentase PBT > 5%', 'type' => 'penggerek batang tebu'],
                    ['per' => $item->per_ppt, 'threshold' => 0.05, 'min_age' => 4, 'max_age' => null, 'title' => 'HPT - Persentase PPT > 5%', 'type' => 'penggerek pucuk tebu']
                ];

                foreach ($notifications as $notif) {
                    if ($notif['per'] > $notif['threshold'] && $umur_tanam >= $notif['min_age'] && ($notif['max_age'] === null || $umur_tanam <= $notif['max_age'])) {
                        Notification::create([
                            'id' => $nextId++,
                            'companycode' => $item->comp,
                            'title' => $notif['title'],
                            'body' => "Persentase {$notif['type']} lebih dari " . ($notif['threshold'] * 100) . "% untuk nomor sample {$item->sample}, nomor urut {$item->urut}, umur tanaman {$umur_tanam} bulan.",
                            'inputby' => 'Automatic by System',
                        ]);
                    }
                }
            }
        });
    }
}
