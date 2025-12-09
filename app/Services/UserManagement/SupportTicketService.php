<?php

namespace App\Services\UserManagement;

use App\Repositories\UserManagement\SupportTicketRepository;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class SupportTicketService
{
    protected SupportTicketRepository $ticketRepository;

    public function __construct(SupportTicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Get paginated tickets
     */
    public function getPaginatedTickets(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->ticketRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get ticket statistics
     */
    public function getStatistics(string $companycode): array
    {
        return $this->ticketRepository->getStatistics($companycode);
    }

    /**
     * Create new ticket (public submission)
     */
    public function createTicket(array $data): array
    {
        try {
            // Validate user exists
            if (!$this->ticketRepository->userExists($data['username'], $data['companycode'])) {
                return [
                    'success' => false,
                    'message' => 'Username tidak ditemukan di company yang dipilih'
                ];
            }

            // Check for duplicate pending ticket
            if ($this->ticketRepository->hasPendingTicket($data['username'], $data['category'])) {
                return [
                    'success' => false,
                    'message' => 'Anda sudah memiliki ticket pending untuk kategori ini. Mohon tunggu response admin.'
                ];
            }

            // Generate ticket number
            $ticketNumber = $this->ticketRepository->generateTicketNumber($data['companycode']);

            $ticketData = [
                'ticket_number' => $ticketNumber,
                'category' => $data['category'],
                'status' => 'open',
                'priority' => $data['priority'] ?? 'medium',
                'fullname' => $data['fullname'],
                'username' => $data['username'],
                'companycode' => $data['companycode'],
                'description' => $data['description'] ?? null,
                'createdat' => now()
            ];

            $ticket = $this->ticketRepository->create($ticketData);

            Log::info('Support ticket created', [
                'ticket_id' => $ticket->ticket_id,
                'ticket_number' => $ticketNumber,
                'username' => $data['username']
            ]);

            return [
                'success' => true,
                'ticket_number' => $ticketNumber,
                'message' => 'Ticket berhasil dibuat. Nomor ticket: ' . $ticketNumber
            ];
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Database error (connection, constraint, etc)
            Log::error('Database error creating ticket', [
                'error' => $e->getMessage(),
                'username' => $data['username'] ?? null,
                'sql_state' => $e->errorInfo[0] ?? null
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan hubungi IT Support atau coba beberapa saat lagi.'
            ];
            
        } catch (\Exception $e) {
            // General error
            Log::error('Failed to create ticket', [
                'error' => $e->getMessage(),
                'username' => $data['username'] ?? null
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan hubungi IT Support atau coba beberapa saat lagi.'
            ];
        }
    }

    /**
     * Update ticket status
     */
    public function updateTicket(int $ticketId, array $data, string $updatedBy): array
    {
        try {
            $ticket = $this->ticketRepository->find($ticketId);

            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket tidak ditemukan'
                ];
            }

            $updateData = [
                'status' => $data['status'],
                'priority' => $data['priority'] ?? $ticket->priority,
                'resolution_notes' => $data['resolution_notes'] ?? $ticket->resolution_notes,
                'updatedat' => now()
            ];

            // Track status changes
            if ($data['status'] === 'in_progress' && $ticket->status !== 'in_progress') {
                $updateData['inprogress_by'] = $updatedBy;
                $updateData['inprogress_at'] = now();
            }

            if (in_array($data['status'], ['resolved', 'closed']) && !in_array($ticket->status, ['resolved', 'closed'])) {
                $updateData['resolved_by'] = $updatedBy;
                $updateData['resolved_at'] = now();
            }

            $this->ticketRepository->update($ticketId, $updateData);

            Log::info('Ticket updated', [
                'ticket_id' => $ticketId,
                'status' => $data['status'],
                'updated_by' => $updatedBy
            ]);

            return [
                'success' => true,
                'message' => 'Ticket berhasil diperbarui'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update ticket', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui ticket'
            ];
        }
    }

    /**
     * Delete ticket
     */
    public function deleteTicket(int $ticketId): array
    {
        try {
            $ticket = $this->ticketRepository->find($ticketId);

            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket tidak ditemukan'
                ];
            }

            $this->ticketRepository->delete($ticketId);

            Log::info('Ticket deleted', [
                'ticket_id' => $ticketId,
                'ticket_number' => $ticket->ticket_number
            ]);

            return [
                'success' => true,
                'message' => 'Ticket berhasil dihapus'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete ticket', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus ticket'
            ];
        }
    }

    /**
     * Get ticket by ID
     */
    public function getTicketById(int $ticketId): ?SupportTicket
    {
        return $this->ticketRepository->find($ticketId);
    }
}