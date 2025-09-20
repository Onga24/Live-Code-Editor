<?php
// App/Events/UserJoined.php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projectId;
    public $user;

    public function __construct($projectId, User $user)
    {
        $this->projectId = $projectId;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('project.' . $this->projectId);
    }

    public function broadcastAs()
    {
        return 'UserJoined';
    }

    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar ?? null
            ],
            'project_id' => $this->projectId,
            'timestamp' => now()->toISOString()
        ];
    }
}