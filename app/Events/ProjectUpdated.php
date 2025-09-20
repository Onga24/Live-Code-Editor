<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Project;

class ProjectUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $project;

    /**
     * Create a new event instance.
     */
    public function __construct(Project $project)
    {
        // هنرسل نسخة fresh علشان نضمن البيانات محدثة
        $this->project = $project->fresh();
    }

    /**
     * The channel on which the event should broadcast.
     */
    public function broadcastOn()
    {
        // قناة خاصة لكل مشروع
        return new PrivateChannel("project.{$this->project->id}");
    }

    /**
     * اسم الحدث عند الاستقبال على الواجهة الأمامية
     */
    public function broadcastAs()
    {
        return 'project.updated';
    }

    /**
     * البيانات اللي هتتبث
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->project->id,
            'title' => $this->project->title,
            'description' => $this->project->description,
            'updated_at' => $this->project->updated_at->toDateTimeString(),
        ];
    }
}
