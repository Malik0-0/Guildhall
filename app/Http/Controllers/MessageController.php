<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Quest;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\SanitizeHelper;

class MessageController extends Controller
{
    /**
     * Show the messaging thread for a quest.
     */
    public function thread($questId)
    {
        $quest = Quest::with(['patron', 'adventurer'])->findOrFail($questId);
        $user = Auth::user();

        // Check if user is part of this quest
        if ($quest->patron_id !== $user->id && $quest->adventurer_id !== $user->id) {
            return redirect()->route('quests.show', $questId)
                ->with('error', 'You can only view messages for quests you are involved in.');
        }

        // Determine the other party
        $otherParty = $quest->patron_id === $user->id 
            ? $quest->adventurer 
            : $quest->patron;

        // Get all messages for this quest
        $messages = Message::forQuest($questId)
            ->with(['sender', 'receiver'])
            ->get();

        // Mark messages as read for current user
        Message::where('quest_id', $questId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return view('messages.thread', compact('quest', 'messages', 'otherParty'));
    }

    /**
     * Send a message for a quest.
     */
    public function send(Request $request, $questId)
    {
        $quest = Quest::with(['patron', 'adventurer'])->findOrFail($questId);
        $user = Auth::user();

        // Check if user is part of this quest
        if ($quest->patron_id !== $user->id && $quest->adventurer_id !== $user->id) {
            return redirect()->back()
                ->with('error', 'You can only send messages for quests you are involved in.');
        }

        // Determine receiver (the other party)
        $receiverId = $quest->patron_id === $user->id 
            ? $quest->adventurer_id 
            : $quest->patron_id;

        if (!$receiverId) {
            return redirect()->back()
                ->with('error', 'The other party is not yet assigned to this quest.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        try {
            $message = Message::create([
                'quest_id' => $quest->id,
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'message' => SanitizeHelper::sanitizeText($validated['message']),
            ]);

            // Load relationships for broadcasting
            $message->load(['sender', 'receiver']);

            // Broadcast the message
            broadcast(new MessageSent($message))->toOthers();

            // If this is an AJAX/JSON request, return JSON
            if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => [
                        'id' => $message->id,
                        'quest_id' => $message->quest_id,
                        'sender_id' => $message->sender_id,
                        'receiver_id' => $message->receiver_id,
                        'message' => $message->message,
                        'sender' => [
                            'id' => $message->sender->id,
                            'name' => $message->sender->name,
                        ],
                        'created_at_human' => $message->created_at->diffForHumans(),
                    ],
                ]);
            }

            return redirect()->route('messages.thread', $questId)
                ->with('success', 'Message sent successfully!');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to send message: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    /**
     * Get unread message count for current user.
     */
    public function unreadCount()
    {
        $count = Message::unreadFor(Auth::id())->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Get unread messages count per quest.
     */
    public function unreadByQuest()
    {
        $unreadMessages = Message::unreadFor(Auth::id())
            ->select('quest_id', DB::raw('count(*) as count'))
            ->groupBy('quest_id')
            ->pluck('count', 'quest_id');

        return response()->json($unreadMessages);
    }
}

