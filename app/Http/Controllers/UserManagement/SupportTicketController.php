<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Support Ticket Controller
 * 
 * Handles support ticket management
 * 
 * Permissions:
 * - usermanagement.supportticket.view
 * - usermanagement.supportticket.edit
 * - usermanagement.supportticket.delete
 */
class SupportTicketController extends Controller
{
    /**
     * Display a listing of support tickets
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $tickets = DB::table('supportticket as st')
                ->leftJoin('user as u', 'st.userid', '=', 'u.userid')
                ->select(
                    'st.*',
                    'u.name as username'
                )
                ->orderBy('st.createdat', 'desc')
                ->get();

            // Group by status for statistics
            $stats = [
                'total' => $tickets->count(),
                'open' => $tickets->where('status', 'OPEN')->count(),
                'in_progress' => $tickets->where('status', 'IN_PROGRESS')->count(),
                'resolved' => $tickets->where('status', 'RESOLVED')->count(),
                'closed' => $tickets->where('status', 'CLOSED')->count(),
            ];

            return view('usermanagement.supportticket.index', [
                'title' => 'Support Ticket',
                'navbar' => 'User Management',
                'nav' => 'Support Ticket',
                'tickets' => $tickets,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading support tickets', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load support tickets');
        }
    }

    /**
     * Store a newly created support ticket (public endpoint)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:200',
            'description' => 'required|string',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'category' => 'nullable|string|max:50',
            'email' => 'required|email|max:100',
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate ticket number
            $ticketNumber = 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $ticketId = DB::table('supportticket')->insertGetId([
                'ticket_number' => $ticketNumber,
                'userid' => auth()->check() ? auth()->user()->userid : null,
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'description' => $request->description,
                'category' => $request->category,
                'priority' => $request->priority,
                'status' => 'OPEN',
                'createdat' => now(),
            ]);

            DB::commit();

            Log::info('Support ticket created', [
                'ticket_id' => $ticketId,
                'ticket_number' => $ticketNumber,
                'email' => $request->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Support ticket submitted successfully',
                'ticket_number' => $ticketNumber,
                'ticket_id' => $ticketId
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating support ticket', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit support ticket'
            ], 500);
        }
    }

    /**
     * Update the specified support ticket
     * 
     * @param Request $request
     * @param int $ticket_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $ticket_id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:OPEN,IN_PROGRESS,RESOLVED,CLOSED',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'assigned_to' => 'nullable|string|exists:user,userid',
            'admin_notes' => 'nullable|string',
            'resolution' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $ticket = DB::table('supportticket')->where('id', $ticket_id)->first();
            if (!$ticket) {
                return back()->with('error', 'Support ticket not found');
            }

            $updateData = [
                'status' => $request->status,
                'priority' => $request->priority,
                'assigned_to' => $request->assigned_to,
                'admin_notes' => $request->admin_notes,
                'resolution' => $request->resolution,
                'updatedat' => now(),
            ];

            // Set resolved_at when status changes to RESOLVED
            if ($request->status === 'RESOLVED' && $ticket->status !== 'RESOLVED') {
                $updateData['resolved_at'] = now();
                $updateData['resolved_by'] = auth()->user()->userid;
            }

            // Set closed_at when status changes to CLOSED
            if ($request->status === 'CLOSED' && $ticket->status !== 'CLOSED') {
                $updateData['closed_at'] = now();
                $updateData['closed_by'] = auth()->user()->userid;
            }

            DB::table('supportticket')
                ->where('id', $ticket_id)
                ->update($updateData);

            DB::commit();

            Log::info('Support ticket updated', [
                'ticket_id' => $ticket_id,
                'status' => $request->status,
                'updated_by' => auth()->user()->userid
            ]);

            return back()->with('success', 'Support ticket updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating support ticket', [
                'ticket_id' => $ticket_id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to update support ticket');
        }
    }

    /**
     * Remove the specified support ticket
     * 
     * @param int $ticket_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($ticket_id)
    {
        try {
            DB::beginTransaction();

            $ticket = DB::table('supportticket')->where('id', $ticket_id)->first();
            if (!$ticket) {
                return back()->with('error', 'Support ticket not found');
            }

            // Hard delete (or you can implement soft delete)
            DB::table('supportticket')
                ->where('id', $ticket_id)
                ->delete();

            DB::commit();

            Log::info('Support ticket deleted', [
                'ticket_id' => $ticket_id,
                'ticket_number' => $ticket->ticket_number,
                'deleted_by' => auth()->user()->userid
            ]);

            return back()->with('success', 'Support ticket deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting support ticket', [
                'ticket_id' => $ticket_id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to delete support ticket');
        }
    }
}