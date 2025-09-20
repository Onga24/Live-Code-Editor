<?php
// App/Events/FileDeleted.php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class FileDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projectId;
    public $fileId;
    public $fileName;
    public $deletedBy;

    public function __construct($projectId, $fileId, $fileName, $deletedBy = null)
    {
        $this->projectId = $projectId;
        $this->fileId = $fileId;
        $this->fileName = $fileName;
        $this->deletedBy = $deletedBy;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('project.' . $this->projectId);
    }

    public function broadcastAs()
    {
        return 'FileDeleted';
    }

    public function broadcastWith()
    {
        return [
            'project_id' => $this->projectId,
            'file_id' => $this->fileId,
            'file_name' => $this->fileName,
            'deleted_by' => $this->deletedBy,
            'timestamp' => now()->toISOString()
        ];
    }

    public function broadcastWhen()
    {
        return $this->projectId && $this->fileId;
    }
}