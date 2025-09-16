<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Project; // 🟢 تأكد من استيراد نموذج المشروع
use App\Models\User; // 🟢 تأكد من استيراد نموذج المستخدم

Broadcast::channel('project.{id}', function ($user, $id) {
    \Log::info('Auth attempt for project channel', [
        'user' => $user ? $user->id : null,
        'id' => $id,
    ]);

    // مؤقتًا رجّع true للتجربة
    return true;
});

// Broadcast::channel('project.{projectId}', function ($user, $projectId) {
//     \Log::info('🔔 Broadcasting auth call', [
//         'user_id'   => $user ? $user->id : null,
//         'user_name' => $user ? $user->name : null,
//         'projectId' => $projectId,
//     ]);

//     $project = Project::find($projectId);

//     if (!$project) {
//         \Log::warning("❌ Project {$projectId} not found");
//         return false;
//     }

//     $isMember = $project->users->contains($user->id);
//     \Log::info("✅ Auth check", [
//         'projectId' => $projectId,
//         'user_id'   => $user->id,
//         'is_member' => $isMember,
//     ]);

//     return $isMember;
// });

// Broadcast::channel('project.{projectId}', function ($user, $projectId) {
//     $project = Project::find($projectId);
//     if (!$project) {
//         return false;
//     }
//     return $project->users->contains($user->id);
// });


// قناة خاصة لمحادثات المشاريع
// تقوم بالتحقق من أن المستخدم هو عضو في المشروع المحدد
// Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    // 1. البحث عن المشروع باستخدام الـ ID.
    // 💡 استخدام `findOrFail` سيتسبب في خطأ 404 إذا لم يتم العثور على المشروع.
    // يفضل استخدام `find` والتحقق يدوياً لتجنب الأخطاء الصامتة.
    // $project = Project::find($projectId);

    // 2. إذا لم يتم العثور على المشروع، نرفض الاتصال مباشرة.
    // if (!$project) {
        // return false;
    // }

    // 3. التحقق من أن المستخدم الحالي هو عضو في هذا المشروع.
    // هذه الخطوة تفترض وجود علاقة `belongsToMany` بين نموذج User و Project.
    // return $project->users->contains($user->id);
// });

// قناة الحضور (Presence) لمحادثات عامة
// يمكنك استخدامها لغرف الدردشة التي يرى فيها المستخدمون بعضهم البعض.
Broadcast::channel('chat.{chatRoom}', function ($user, $chatRoom) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
