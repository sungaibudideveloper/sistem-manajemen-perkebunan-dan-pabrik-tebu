<?php

namespace App\Repositories\UserManagement;

use App\Models\SupportTicket;
use Illuminate\Pagination\LengthAwarePaginator;

class SupportTicketRepository
{
    /**
     * Get paginated tickets with filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SupportTicket::with(['company', 'user']);

        // Apply company filter
        if (!empty($filters['companycode'])) {
            $query->where('companycode', $filters['companycode']);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply category filter
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('fullname', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('createdat', 'desc')->paginate($perPage);
    }

    /**
     * Find ticket by ID
     */
    public function find(int $ticketId): ?SupportTicket
    {
        return SupportTicket::with(['company', 'user'])->find($ticketId);
    }

    /**
     * Create new ticket
     */
    public function create(array $data): SupportTicket
    {
        return SupportTicket::create($data);
    }

    /**
     * Update ticket
     */
    public function update(int $ticketId, array $data): bool
    {
        return SupportTicket::where('ticket_id', $ticketId)->update($data);
    }

    /**
     * Delete ticket
     */
    public function delete(int $ticketId): bool
    {
        return SupportTicket::where('ticket_id', $ticketId)->delete();
    }

    /**
     * Get statistics
     */
    public function getStatistics(string $companycode): array
    {
        $query = SupportTicket::where('companycode', $companycode);

        return [
            'total' => $query->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count()
        ];
    }

    /**
     * Check if user exists
     */
    public function userExists(string $username, string $companycode): bool
    {
        return \DB::table('user')
            ->where('userid', $username)
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->exists();
    }

    /**
     * Check for duplicate pending ticket
     */
    public function hasPendingTicket(string $username, string $category, int $hours = 24): bool
    {
        return SupportTicket::where('username', $username)
            ->where('category', $category)
            ->where('status', 'open')
            ->where('createdat', '>', now()->subHours($hours))
            ->exists();
    }

    public function generateTicketNumber(string $companycode): string
    {
        $prefix = 'TKT-' . $companycode . '-' . date('Ymd');
        
        $lastTicket = SupportTicket::where('ticket_number', 'LIKE', $prefix . '%')
            ->orderBy('ticket_id', 'desc')
            ->first();

        if ($lastTicket) {
            $lastSeq = (int) substr($lastTicket->ticket_number, -4);
            $newSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSeq = '0001';
        }

        return $prefix . '-' . $newSeq;
    }
}