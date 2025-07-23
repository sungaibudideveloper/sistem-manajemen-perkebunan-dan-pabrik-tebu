<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Send a message
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:500',
            ]);

            $user = Auth::user();
            $message = $request->input('message');

            // Broadcast the message
            broadcast(new MessageSent($message, [
                'id' => $user->id,
                'name' => $user->name,
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'message' => $message,
                    'user' => [
                        'id' => $userId,
                        'name' => $userName,
                    ],
                    'timestamp' => now()->format('H:i')
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Chat send message error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }
}