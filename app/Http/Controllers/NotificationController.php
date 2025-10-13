<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function index()
    {
        $title = 'Notifications';
        $userid = Auth::user()->userid;
        
        $notifications = Notification::getForUser($userid, 1000, false);
        $notifCount = $notifications->count();
        $unreadCount = $notifications->where('is_read', false)->count();

        return view('notifications.index', compact(
            'title',
            'notifications',
            'notifCount',
            'unreadCount'
        ));
    }

    public function getDropdownData()
    {
        $userid = Auth::user()->userid;
        $notifications = Notification::getForUser($userid, 5, false);
        $unreadCount = Notification::getUnreadCountForUser($userid);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function getUnreadCount()
    {
        $userid = Auth::user()->userid;
        $unreadCount = Notification::getUnreadCountForUser($userid);

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    public function markAsRead($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $userid = Auth::user()->userid;

            $notification->markAsReadBy($userid);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as read'
            ], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            $userid = Auth::user()->userid;
            $user = Auth::user();
            
            $userCompanies = $user->userCompanies;
            
            if ($userCompanies->isEmpty()) {
                $userCompanyArray = $user->companycode ? explode(',', $user->companycode) : [];
            } else {
                $userCompanyArray = $userCompanies->pluck('companycode')->toArray();
            }
            
            $userCompanyArray = array_filter($userCompanyArray);
            $idjabatan = $user->idjabatan;

            $query = Notification::active()
                                 ->forCompanies($userCompanyArray)
                                 ->unreadBy($userid);

            if ($idjabatan) {
                $query->forJabatan($idjabatan);
            }

            $notifications = $query->get();
            
            foreach ($notifications as $notification) {
                $notification->markAsReadBy($userid);
            }

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'count' => $notifications->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all as read'
            ], 500);
        }
    }

    public function adminIndex()
    {
        if (!hasPermission('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $title = 'Notification Management';
        $search = request('search');
        $perPage = request('perPage', 15);

        $query = Notification::with('supportTicket');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhere('companycode', 'like', "%{$search}%");
            });
        }

        $result = $query->orderBy('createdat', 'desc')->paginate($perPage);
        $companies = Company::orderBy('name')->get();

        return view('notifications.admin.index', compact('title', 'result', 'companies', 'perPage'));
    }

    public function create()
    {
        if (!hasPermission('Create Notifikasi')) {
            abort(403, 'Unauthorized action.');
        }

        $title = 'Create Notification';
        $companies = Company::orderBy('name')->get();
        $jabatan = \App\Models\Jabatan::orderBy('namajabatan')->get();

        return view('notifications.create', compact('title', 'companies', 'jabatan'));
    }

    public function store(Request $request)
    {
        if (!hasPermission('Create Notifikasi')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'companycodes' => 'required|array',
            'companycodes.*' => 'exists:company,companycode',
            'target_jabatan' => 'nullable|array',
            'target_jabatan.*' => 'integer|exists:jabatan,idjabatan',
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'action_url' => 'nullable|url'
        ]);

        try {
            $notificationData = [
                'companycodes' => $validated['companycodes'],
                'title' => $validated['title'],
                'body' => $validated['body'],
                'priority' => $validated['priority'],
                'action_url' => $validated['action_url'] ?? null
            ];

            if (!empty($validated['target_jabatan'])) {
                $filteredJabatan = array_filter($validated['target_jabatan']);
                $notificationData['target_jabatan'] = implode(',', $filteredJabatan);
            }

            Notification::createManualNotification($notificationData);

            return redirect()->route('notifications.admin.index')
                           ->with('success', 'Notification berhasil dibuat dan dikirim');

        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal membuat notification: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        if (!hasPermission('Edit Notifikasi')) {
            abort(403, 'Unauthorized action.');
        }

        $notification = Notification::findOrFail($id);
        
        if ($notification->notification_type !== 'manual') {
            return redirect()->route('notifications.admin.index')
                           ->with('error', 'Hanya manual notification yang bisa diedit');
        }

        $title = 'Edit Notification';
        $companies = Company::orderBy('name')->get();
        $jabatan = \App\Models\Jabatan::orderBy('namajabatan')->get();

        return view('notifications.edit', compact('title', 'notification', 'companies', 'jabatan'));
    }

    public function update(Request $request, $id)
    {
        if (!hasPermission('Edit Notifikasi')) {
            abort(403, 'Unauthorized action.');
        }

        $notification = Notification::findOrFail($id);

        if ($notification->notification_type !== 'manual') {
            return redirect()->route('notifications.admin.index')
                           ->with('error', 'Hanya manual notification yang bisa diedit');
        }

        $validated = $request->validate([
            'companycodes' => 'required|array',
            'companycodes.*' => 'exists:company,companycode',
            'target_jabatan' => 'nullable|array',
            'target_jabatan.*' => 'integer|exists:jabatan,idjabatan',
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'action_url' => 'nullable|url'
        ]);

        try {
            $updateData = [
                'companycode' => implode(',', $validated['companycodes']),
                'title' => $validated['title'],
                'body' => $validated['body'],
                'priority' => $validated['priority'],
                'action_url' => $validated['action_url'] ?? null,
                'updatedat' => now()
            ];

            if (!empty($validated['target_jabatan'])) {
                $updateData['target_jabatan'] = implode(',', $validated['target_jabatan']);
            } else {
                $updateData['target_jabatan'] = null;
            }

            $notification->update($updateData);

            return redirect()->route('notifications.admin.index')
                           ->with('success', 'Notification berhasil diperbarui');

        } catch (\Exception $e) {
            Log::error('Failed to update notification', [
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal memperbarui notification: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!hasPermission('Hapus Notifikasi')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notification = Notification::findOrFail($id);

            $notification->update([
                'status' => 'deleted',
                'updatedat' => now()
            ]);

            return redirect()->route('notifications.admin.index')
                           ->with('success', 'Notification berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Failed to delete notification', [
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('notifications.admin.index')
                           ->with('error', 'Gagal menghapus notification: ' . $e->getMessage());
        }
    }

    public static function notifyNewSupportTicket($ticket)
    {
        try {
            Notification::createForSupportTicket($ticket);
            
            Log::info('Support ticket notification created', [
                'ticket_id' => $ticket->ticket_id,
                'ticket_number' => $ticket->ticket_number
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create support ticket notification', [
                'ticket_id' => $ticket->ticket_id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }
}