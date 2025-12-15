<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserManagement\SupportTicketService;
use App\Models\MasterData\Company;

class SupportTicketController extends Controller
{
    protected SupportTicketService $ticketService;

    public function __construct(SupportTicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display ticket listing
     */
    public function index(Request $request)
    {
        $companycode = session('companycode');
        
        $filters = [
            'search' => $request->get('search'),
            'companycode' => $companycode,
            'status' => $request->get('status'),
            'category' => $request->get('category')
        ];

        $perPage = $request->get('perPage', 15);
        $result = $this->ticketService->getPaginatedTickets($filters, $perPage);
        $stats = $this->ticketService->getStatistics($companycode);

        $companies = Company::orderBy('name')->get();

        return view('usermanagement.support-ticket.index', [
            'title' => 'Support Tickets',
            'navbar' => 'User Management',
            'nav' => 'Support Tickets',
            'result' => $result,
            'companies' => $companies,
            'stats' => $stats,
            'perPage' => $perPage,
            'companycode' => $companycode
        ]);
    }

    /**
     * Show ticket detail
     */
    public function show($ticketId)
    {
        $ticket = $this->ticketService->getTicketById($ticketId);

        if (!$ticket) {
            return redirect()->route('usermanagement.support-ticket.index')
                ->with('error', 'Ticket tidak ditemukan');
        }

        return view('usermanagement.support-ticket.show', [
            'title' => 'Ticket Detail',
            'navbar' => 'User Management',
            'nav' => 'Ticket Detail',
            'ticket' => $ticket
        ]);
    }

    /**
     * Public ticket submission (no auth)
     */
    public function publicStore(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:100',
            'username' => 'required|string|max:50',
            'companycode' => 'required|string|max:4|exists:company,companycode',
            'category' => 'required|in:forgot_password,bug_report,support,other',
            'description' => 'nullable|string|max:1000',
            'g-recaptcha-response' => 'required'
        ]);

        $result = $this->ticketService->createTicket($validated);

        if ($result['success']) {
            return redirect()->route('login')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Update ticket
     */
    public function update(Request $request, $ticketId)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|in:low,medium,high',
            'resolution_notes' => 'nullable|string'
        ]);

        $result = $this->ticketService->updateTicket(
            $ticketId,
            $validated,
            auth()->user()->userid
        );

        return redirect()->back()
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Delete ticket
     */
    public function destroy($ticketId)
    {
        $result = $this->ticketService->deleteTicket($ticketId);

        return redirect()->route('usermanagement.support-ticket.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}