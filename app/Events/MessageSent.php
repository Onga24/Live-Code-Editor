<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }


    //  public function broadcastOn() {
    //     return new PrivateChannel('project.' . $this->message->project_id);
    // }

    

    public function broadcastOn()
    {
        // إذا كانت الرسالة مرتبطة بمشروع
        if ($this->message->project_id) {
            echo "hi in message sent event my message is " . $this->message . " : " . $this->message->project_id;
            return new PrivateChannel('project.' . $this->message->project_id);
        }
        
        // إذا كانت رسالة في غرفة عامة
        return new PresenceChannel('chat.' . ($this->message->chat_room ?? 'general'));
    }

    public function broadcastAs() {
        return 'MessageSent';
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'created_at' => $this->message->created_at,
                'user' => [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->name,
                ]
            ]
        ];
    }
}