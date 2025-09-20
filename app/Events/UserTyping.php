<?php
// App/Events/UserTyping.php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projectId;
    public $fileId;
    public $user;
    public $isTyping;

    public function __construct($projectId, $fileId, User $user, $isTyping)
    {
        $this->projectId = $projectId;
        $this->fileId = $fileId;
        $this->user = $user;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('project.' . $this->projectId);
    }

    public function broadcastAs()
    {
        return 'UserTyping';
    }

    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name
            ],
            'file_id' => $this->fileId,
            'is_typing' => $this->isTyping,
            'project_id' => $this->projectId,
            'timestamp' => now()->toISOString()
        ];
    }
}