<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
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
            'kd_comp' => 'required',
            'title' => 'required',
            'body' => 'required',
        ];
    }

    public function index()
    {
        $title = 'Notifications';
        $comp = explode(',', Auth::user()->userComp->kd_comp);
        $dropdownValue = session('dropdown_value');

        $permissions = json_decode(Auth::user()->permissions, true);
        $isKepalaKebun = in_array('Kepala Kebun', $permissions);
        $isAdmin = in_array('Admin', $permissions);

        $notifQuery = DB::table('notification')
            ->join('perusahaan', function ($join) {
                $join->whereRaw('FIND_IN_SET(perusahaan.kd_comp, notification.kd_comp)');
            });

        if ($isAdmin) {
            $notifQuery->where(function ($query) use ($comp) {
                foreach ($comp as $company) {
                    $query->orWhereRaw('FIND_IN_SET(?, notification.kd_comp)', [$company]);
                }
            })->distinct();
        } else {
            $notifQuery->whereRaw('FIND_IN_SET(?, notification.kd_comp)', [session('dropdown_value')])->distinct();
        }

        if (!$isKepalaKebun) {
            $notifQuery->where('notification.user_input', '!=', 'Automatic by System');
        }

        $notif = $notifQuery->whereBetween('notification.created_at', [DB::raw('perusahaan.tgl'), now()])
            ->select('notification.*')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at);
                return $item;
            });

        $notifCount = $notif->count();

        return view('notifications.index', compact('title', 'notif', 'notifCount'));
    }

    public function create()
    {
        $title = "Create Data";
        $notification = new Notification();
        $company = Perusahaan::all();
        $method = 'POST';
        $buttonSubmit = 'Create';
        $url = route('notifications.store');
        return view('notifications.form', compact('title', 'company', 'notification', 'method', 'url', 'buttonSubmit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());

        DB::transaction(function () use ($validated) {
            $kd_comp = implode(',', $validated['kd_comp']);
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
                'kd_comp' => $kd_comp,
                'title' => $validated['title'],
                'body' => $validated['body'],
                'user_input' => Auth::user()->usernm,
            ]);
        });

        return redirect()->back()->with('success1', 'Notifikasi Telah ditambahkan.');
    }

    public function edit($id)
    {
        $title = 'Edit Data';
        $notification = Notification::findOrFail($id);
        $company = Perusahaan::all();
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
        $readBy = $notification->read_by ? json_decode($notification->read_by, true) : [];

        if (!in_array($currentUser, $readBy)) {
            $readBy[] = $currentUser;
            $notification->read_by = json_encode($readBy);
            $notification->save();
        }

        return response()->json(['message' => 'Notification marked as read'], 200);
    }

    public function getUnreadCount()
    {
        $currentUser = Auth::user()->usernm;
        $comp = explode(',', Auth::user()->userComp->kd_comp);
        $permissions = json_decode(Auth::user()->permissions, true);
        $isKepalaKebun = in_array('Kepala Kebun', $permissions);
        $isAdmin = in_array('Admin', $permissions);

        $query = DB::table('notification')
            ->join('perusahaan', function ($join) {
                $join->whereRaw('FIND_IN_SET(perusahaan.kd_comp, notification.kd_comp)');
            });

        if ($isAdmin) {
            $query->where(function ($query) use ($comp, $currentUser) {
                $query->where(function ($query) use ($comp) {
                    foreach ($comp as $kdComp) {
                        $query->orWhereRaw('FIND_IN_SET(?, notification.kd_comp)', [$kdComp]);
                    }
                })
                    ->where(function ($query) use ($currentUser) {
                        $query->where('notification.read_by', '=', '')
                            ->orWhereRaw('NOT JSON_CONTAINS(notification.read_by, ?)', [json_encode($currentUser)]);
                    });
            })
                ->distinct();
        } else {
            $query->whereRaw('FIND_IN_SET(?, notification.kd_comp)', [session('dropdown_value')])
                ->where(function ($query) use ($currentUser) {
                    $query->where('notification.read_by', '=', '')
                        ->orWhereRaw('NOT JSON_CONTAINS(notification.read_by, ?)', [json_encode($currentUser)]);
                })
                ->distinct();
        }

        if (!$isKepalaKebun) {
            $query->where('notification.user_input', '!=', 'Automatic by System');
        }

        $notif = $query->whereBetween('notification.created_at', [DB::raw('perusahaan.tgl'), now()])
            ->select('notification.*')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at);
                return $item;
            });

        $unreadCount = $notif->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    public function agronomiNotif()
    {
        DB::transaction(function () {
            $lastCheckedTime = DB::table('notification')
                ->max('created_at');

            if (!$lastCheckedTime) {
                $lastCheckedTime = '2025-01-01 00:00:00';
            }
            $data = DB::table('agro_lst')
                ->select('kd_comp as comp', 'no_sample as sample', 'no_urut as urut', 'per_germinasi', 'per_gulma', 'tgltanam')
                ->where('created_at', '>', $lastCheckedTime)
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
                if ($item->tgltanam) {
                    $item->umur_tanam = Carbon::parse($item->tgltanam)->diffInMonths(Carbon::now());
                } else {
                    $item->umur_tanam = null;
                }

                if ($item->per_germinasi < 0.9 && ceil($item->umur_tanam) == 1.0) {
                    Notification::create([
                        'id' => $nextId,
                        'kd_comp' => $item->comp,
                        'title' => 'Agronomi - Persentase Germinasi < 90%',
                        'body' => 'Persentase Germinasi kurang dari 90% untuk nomor sample ' . $item->sample . ', nomor urut ' . $item->urut . ', berumur ' . ceil($item->umur_tanam) . ' bulan.',
                        'user_input' => 'Automatic by System',
                    ]);
                    $nextId++;
                }

                if ($item->per_gulma > 0.25) {
                    Notification::create([
                        'id' => $nextId,
                        'kd_comp' => $item->comp,
                        'title' => 'Agronomi - Persentase Penutupan Gulma > 25%',
                        'body' => 'Persentase Penutupan Gulma lebih dari 25% untuk nomor sample ' . $item->sample . ', nomor urut ' . $item->urut . '.',
                        'user_input' => 'Automatic by System',
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
                ->max('created_at');

            if (!$lastCheckedTime) {
                $lastCheckedTime = '2025-01-01 00:00:00';
            }
            $data = DB::table('hpt_lst')
                ->select('kd_comp as comp', 'no_sample as sample', 'no_urut as urut', 'per_pbt', 'tgltanam')
                ->where('created_at', '>', $lastCheckedTime)
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
                $item->umur_tanam = $item->tgltanam ? Carbon::parse($item->tgltanam)->diffInMonths(Carbon::now()) : null;
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
                            'kd_comp' => $item->comp,
                            'title' => $notif['title'],
                            'body' => "Persentase {$notif['type']} lebih dari " . ($notif['threshold'] * 100) . "% untuk nomor sample {$item->sample}, nomor urut {$item->urut}, umur tanaman {$umur_tanam} bulan.",
                            'user_input' => 'Automatic by System',
                        ]);
                    }
                }
            }
        });
    }
}
