<?php
namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // 🟢 جلب رسائل المشروع
    public function projectMessages(Project $project)
    {
        $messages = $project->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->take(100)
            ->get();

        return response()->json([
            'messages' => $messages
        ]);
    }

    // 🟢 إرسال رسالة مرتبطة بمشروع
    public function storeProjectMessage(Request $request, Project $project)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = $project->messages()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        $message->load('user');
        // event(new MessageSent($message));
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message
        ], 201);
    }

    // 🟢 الغرف العامة (زي ما هو عندك)
    public function index(Request $request)
    {
        $chatRoom = $request->get('room', 'general');

        $messages = Message::where('chat_room', $chatRoom)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'messages' => $messages
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'chat_room' => 'string|max:255'
        ]);

        $message = Message::create([
            'user_id'   => Auth::id(),
            'content'   => $request->content,
            'chat_room' => $request->chat_room ?? 'general',
        ]);

        $message->load('user');
        // event(new MessageSent($message));
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message
        ], 201);
    }
}
