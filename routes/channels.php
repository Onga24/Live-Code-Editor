<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Project;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// قناة خاصة بالمشروع
Broadcast::channel('project.{projectId}', function (User $user, $projectId) {
    \Log::info('Broadcasting auth attempt', [
        'user_id' => $user->id,
        'project_id' => $projectId,
        'channel' => "project.{$projectId}"
    ]);

    // تحقق من أن المستخدم عضو في المشروع
    $project = Project::find($projectId);
    
    if (!$project) {
        \Log::warning('Project not found', ['project_id' => $projectId]);
        return false;
    }

    $isMember = $project->members()->where('user_id', $user->id)->exists();
    
    \Log::info('Project membership check', [
        'user_id' => $user->id,
        'project_id' => $projectId,
        'is_member' => $isMember
    ]);

    return $isMember ? $user->only(['id', 'name']) : false;
});

// قناة عامة للشات
Broadcast::channel('chat.{roomName}', function (User $user, $roomName) {
    \Log::info('Chat channel auth', [
        'user_id' => $user->id,
        'room' => $roomName
    ]);

    // يمكن لأي مستخدم مسجل الانضمام للغرف العامة
    return $user->only(['id', 'name']);
});