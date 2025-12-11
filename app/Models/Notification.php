<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Notification extends Model
{
    protected $table = 'notification';
    protected $primaryKey = 'notification_id';
    public $timestamps = false;

    protected $fillable = [
        'notification_type',
        'reference_type',
        'reference_id',
        'companycode',
        'target_jabatan',
        'title',
        'body',
        'action_url',
        'icon',
        'priority',
        'status',
        'readby',
        'inputby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'readby' => 'array',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    protected $attributes = [
        'notification_type' => 'manual',
        'status' => 'active',
        'priority' => 'medium',
        'icon' => 'bell'
    ];

    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class, 'reference_id', 'ticket_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForCompany($query, $companycode)
    {
        return $query->whereRaw("FIND_IN_SET(?, companycode)", [$companycode]);
    }

    public function scopeForCompanies($query, array $companycodes)
    {
        return $query->where(function ($q) use ($companycodes) {
            foreach ($companycodes as $code) {
                $q->orWhereRaw("FIND_IN_SET(?, companycode)", [$code]);
            }
        });
    }

    public function scopeUnreadBy($query, $userid)
    {
        return $query->where(function ($q) use ($userid) {
            $q->whereNull('readby')
                ->orWhereRaw('NOT JSON_CONTAINS(readby, ?)', [json_encode($userid)]);
        });
    }

    public function scopeForJabatan($query, $idjabatan)
    {
        return $query->where(function ($q) use ($idjabatan) {
            $q->whereNull('target_jabatan')
                ->orWhereRaw("FIND_IN_SET(?, target_jabatan)", [$idjabatan]);
        });
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function isReadBy($userid)
    {
        if (!$this->readby) {
            return false;
        }

        return in_array($userid, $this->readby);
    }

    public function markAsReadBy($userid)
    {
        $readBy = $this->readby ?? [];

        if (!in_array($userid, $readBy)) {
            $readBy[] = $userid;
            $this->readby = $readBy;
            $this->updatedat = now();
            $this->save();

            return true;
        }

        return false;
    }

    public function markAsUnreadBy($userid)
    {
        $readBy = $this->readby ?? [];

        if (($key = array_search($userid, $readBy)) !== false) {
            unset($readBy[$key]);
            $this->readby = array_values($readBy);
            $this->updatedat = now();
            $this->save();

            return true;
        }

        return false;
    }

    public function getReadCountAttribute()
    {
        return $this->readby ? count($this->readby) : 0;
    }

    public function isExpired()
    {
        return $this->createdat->lt(now()->subDays(90));
    }

    public static function createForSupportTicket($ticket)
    {
        return self::create([
            'notification_type' => 'support_ticket',
            'reference_type' => 'support_ticket',
            'reference_id' => $ticket->ticket_id,
            'companycode' => $ticket->companycode,
            'target_jabatan' => '7,10',
            'title' => "Support Ticket Baru #{$ticket->ticket_number}",
            'body' => "User {$ticket->fullname} ({$ticket->username}) telah membuat support ticket kategori \"{$ticket->category}\". Mohon segera ditindaklanjuti.",
            'action_url' => route('usermanagement.ticket.index'),
            'icon' => 'ticket',
            'priority' => $ticket->category === 'forgot_password' ? 'high' : 'medium',
            'inputby' => 'system'
        ]);
    }

    public static function createForAgronomi(array $data)
    {
        $condition = $data['condition'];
        $avgGerminasi = $condition['germinasi'];
        $avgGulma = $condition['gulma'];
        $umurTanam = $condition['umur'];
        $plot = $data['plot'];

        // Tentukan severity
        $priority = 'medium';
        $alerts = [];

        if ($avgGerminasi < 0.9 && $umurTanam == 1.0) {
            $alerts[] = "Germinasi rendah (" . round($avgGerminasi * 100, 1) . "%) pada plot " . $plot . ".";
            $priority = 'high';
        }

        if ($avgGulma > 0.25) {
            $alerts[] = "Persentase penutupan gulma tinggi (" . round($avgGulma * 100, 1) . "%) pada plot " . $plot . ".";
            $priority = 'high';
        }

        return self::create([
            'notification_type' => 'agronomi_alert',
            'reference_type' => 'agronomi',
            'reference_id' => $data['plot'],
            'companycode' => $data['companycode'],
            'target_jabatan' => '7,10', // Admin/Supervisor
            'title' => "Agronomi Alert - Plot #{$data['plot']}",
            'body' => "Perhatian: " . implode(', ', $alerts) . ". Mohon segera ditindaklanjuti.",
            'action_url' => route('transaction.agronomi.index'),
            'icon' => 'bell',
            'priority' => $priority,
            'inputby' => Auth::user()->userid ?? 'system'
        ]);
    }

    public static function createForHPT(array $data)
    {
        $condition = $data['condition'];
        $avgPPT = $condition['ppt'];
        $avgPBT = $condition['pbt'];
        $umurTanam = $condition['umur'];
        $plot = $data['plot'];

        // Tentukan severity
        $priority = 'medium';
        $alerts = [];

        $notifications = [
            ['per' => $avgPBT, 'threshold' => 0.03, 'min_age' => 1, 'max_age' => 3, 'title' => 'HPT - Persentase PBT > 3%', 'type' => 'penggerek batang tebu'],
            ['per' => $avgPPT, 'threshold' => 0.03, 'min_age' => 1, 'max_age' => 3, 'title' => 'HPT - Persentase PPT > 3%', 'type' => 'penggerek pucuk tebu'],
            ['per' => $avgPBT, 'threshold' => 0.05, 'min_age' => 4, 'max_age' => null, 'title' => 'HPT - Persentase PBT > 5%', 'type' => 'penggerek batang tebu'],
            ['per' => $avgPPT, 'threshold' => 0.05, 'min_age' => 4, 'max_age' => null, 'title' => 'HPT - Persentase PPT > 5%', 'type' => 'penggerek pucuk tebu']
        ];

        foreach ($notifications as $notif) {
            if (
                $notif['per'] > $notif['threshold'] &&
                $umurTanam >= $notif['min_age'] &&
                ($notif['max_age'] === null || $umurTanam <= $notif['max_age'])
            ) {
                $alerts[] = "Persentase {$notif['type']} lebih dari " . ($notif['threshold'] * 100) . " (" . round($notif['per'] * 100, 1) . "%) pada plot " . $plot . ".";
                $priority = 'high';
            }
        }

        return self::create([
            'notification_type' => 'hpt_alert',
            'reference_type' => 'hpt',
            'reference_id' => $data['plot'],
            'companycode' => $data['companycode'],
            'target_jabatan' => '7,10', // Admin/Supervisor
            'title' => "HPT Alert - Plot #{$data['plot']}",
            'body' => "Perhatian: " . implode(', ', $alerts) . ". Mohon segera ditindaklanjuti.",
            'action_url' => route('transaction.hpt.index'),
            'icon' => 'bell',
            'priority' => $priority,
            'inputby' => Auth::user()->userid ?? 'system'
        ]);
    }

    public static function createManualNotification(array $data)
    {
        if (isset($data['companycodes']) && is_array($data['companycodes'])) {
            $data['companycode'] = implode(',', $data['companycodes']);
            unset($data['companycodes']);
        }

        $data['notification_type'] = 'manual';
        $data['inputby'] = Auth::user()->userid ?? 'system';

        return self::create($data);
    }

    public static function bulkMarkAsRead(array $notificationIds, $userid)
    {
        $notifications = self::whereIn('notification_id', $notificationIds)->get();

        foreach ($notifications as $notification) {
            $notification->markAsReadBy($userid);
        }

        return $notifications->count();
    }

    public static function getForUser($userid, $limit = 10, $unreadOnly = false)
    {
        $user = \App\Models\User::find($userid);

        if (!$user) {
            return collect();
        }

        $userCompanies = $user->userCompanies;

        if ($userCompanies->isEmpty()) {
            $companycodes = $user->companycode ? explode(',', $user->companycode) : [];
        } else {
            $companycodes = $userCompanies->pluck('companycode')->toArray();
        }

        $companycodes = array_filter($companycodes);

        if (empty($companycodes)) {
            return collect();
        }

        $idjabatan = $user->idjabatan;

        $query = self::active()->forCompanies($companycodes);

        if ($idjabatan) {
            $query->forJabatan($idjabatan);
        }

        if ($unreadOnly) {
            $query->unreadBy($userid);
        }

        return $query->orderBy('priority', 'desc')
            ->orderBy('createdat', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notif) use ($userid) {
                $notif->is_read = $notif->isReadBy($userid);
                return $notif;
            });
    }

    public static function getUnreadCountForUser($userid)
    {
        $user = \App\Models\User::find($userid);

        if (!$user) {
            return 0;
        }

        $userCompanies = $user->userCompanies;

        if ($userCompanies->isEmpty()) {
            $companycodes = $user->companycode ? explode(',', $user->companycode) : [];
        } else {
            $companycodes = $userCompanies->pluck('companycode')->toArray();
        }

        $companycodes = array_filter($companycodes);

        if (empty($companycodes)) {
            return 0;
        }

        $idjabatan = $user->idjabatan;

        $query = self::active()
            ->forCompanies($companycodes)
            ->unreadBy($userid);

        if ($idjabatan) {
            $query->forJabatan($idjabatan);
        }

        return $query->count();
    }

    public static function archiveOldNotifications($days = 90)
    {
        return self::where('status', 'active')
            ->where('createdat', '<', now()->subDays($days))
            ->update(['status' => 'archived', 'updatedat' => now()]);
    }
}