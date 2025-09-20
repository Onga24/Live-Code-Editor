<?php
// App/Events/MultipleFilesDeleted.php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class MultipleFilesDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projectId;
    public $deletedFiles; // array of files [{id: 1, name: 'file.txt'}, ...]
    public $deletedBy;

    public function __construct($projectId, array $deletedFiles, $deletedBy = null)
    {
        $this->projectId = $projectId;
        $this->deletedFiles = $deletedFiles;
        $this->deletedBy = $deletedBy;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('project.' . $this->projectId);
    }

    public function broadcastAs()
    {
        return 'MultipleFilesDeleted';
    }

    public function broadcastWith()
    {
        return [
            'project_id' => $this->projectId,
            'deleted_files' => $this->deletedFiles,
            'deleted_by' => $this->deletedBy,
            'timestamp' => now()->toISOString(),
            'count' => count($this->deletedFiles)
        ];
    }

    public function broadcastWhen()
    {
        return !empty($this->deletedFiles);
    }
}