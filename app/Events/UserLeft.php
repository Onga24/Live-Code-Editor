<?php
// App/Events/UserLeft.php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserLeft implements ShouldBroadcast
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
        return 'UserLeft';
    }

    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name
            ],
            'project_id' => $this->projectId,
            'timestamp' => now()->toISOString()
        ];
    }
}