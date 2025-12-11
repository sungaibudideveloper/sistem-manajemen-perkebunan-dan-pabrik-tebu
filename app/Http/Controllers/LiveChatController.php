<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class LiveChatController extends Controller
{
    /**
     * Get recent chat messages
     */
    public function getMessages()
    {
        try {
            $messages = ChatMessage::getRecentMessages();
            
            return response()->json([
                'success' => true,
                'messages' => $messages->map(function($msg) {
                    return [
                        'id' => $msg->id,
                        'message' => $msg->message,
                        'user' => [
                            'id' => $msg->user_id,
                            'name' => $msg->user_name,
                        ],
                        'timestamp' => $msg->created_at->format('H:i'),
                        'isOwn' => $msg->user_id === auth()->user()->userid,
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Chat messages error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'messages' => [],
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to load messages'
            ]);
        }
    }

    /**
     * Send a message
     */
    public function send(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:500',
            ]);

            $user = Auth::user();

            // Save to database
            $chatMessage = ChatMessage::create([
                'user_id' => $user->userid,
                'user_name' => $user->name,
                'message' => $request->message,
            ]);

            // Broadcast message to other users
            broadcast(new MessageSent($request->message, [
                'id' => $user->userid,
                'name' => $user->name,
            ]))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Chat send error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to send message'
            ], 500);
        }
    }
}