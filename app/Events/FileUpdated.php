<?php
// App/Events/FileUpdated.php

namespace App\Events;

use App\Models\ProjectFile;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class FileUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $file;
    public $updatedBy;
    public $changeType; // للتمييز بين أنواع التغييرات

    public function __construct(ProjectFile $file, $updatedBy = null, $changeType = 'content_update')
    {
        $this->file = $file;
        $this->updatedBy = $updatedBy;
        $this->changeType = $changeType;
    }

    public function broadcastOn()
    {
        // ✅ استخدام قناة المشروع المصححة
        return new PrivateChannel('project.' . $this->file->project_id);
    }

    public function broadcastAs()
    {
        // ✅ تحسين اسم الحدث ليكون أكثر وضوحاً
        return 'FileUpdated';
    }

    public function broadcastWith()
    {
        return [
            'file' => [
                'id' => $this->file->id,
                'name' => $this->file->original_name,
                'content' => $this->file->content,
                'updated_at' => $this->file->updated_at,
                'updated_by' => $this->updatedBy,
                'project_id' => $this->file->project_id // ✅ إضافة معرف المشروع
            ],
            'project_id' => $this->file->project_id,
            'change_type' => $this->changeType, // ✅ نوع التغيير
            'user_id' => $this->updatedBy,
            'timestamp' => now()->toISOString()
        ];
    }

    // ✅ إضافة شروط البث
    public function broadcastWhen()
    {
        return $this->file->project_id !== null;
    }
}