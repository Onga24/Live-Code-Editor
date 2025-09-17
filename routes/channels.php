<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Project; // 🟢 تأكد من استيراد نموذج المشروع
use App\Models\User; // 🟢 تأكد من استيراد نموذج المستخدم

// قناة خاصة لمحادثات المشاريع
// تقوم بالتحقق من أن المستخدم هو عضو في المشروع المحدد
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    // 1. البحث عن المشروع باستخدام الـ ID.
    // 💡 استخدام `findOrFail` سيتسبب في خطأ 404 إذا لم يتم العثور على المشروع.
    // يفضل استخدام `find` والتحقق يدوياً لتجنب الأخطاء الصامتة.
    $project = Project::find($projectId);

    // 2. إذا لم يتم العثور على المشروع، نرفض الاتصال مباشرة.
    if (!$project) {
        return false;
    }

    // 3. التحقق من أن المستخدم الحالي هو عضو في هذا المشروع.
    // هذه الخطوة تفترض وجود علاقة `belongsToMany` بين نموذج User و Project.
    return $project->users->contains($user->id);
});

// قناة الحضور (Presence) لمحادثات عامة
// يمكنك استخدامها لغرف الدردشة التي يرى فيها المستخدمون بعضهم البعض.
Broadcast::channel('chat.{chatRoom}', function ($user, $chatRoom) {
    // 💡 يمكنك إضافة منطق للتحقق من أن المستخدم لديه إذن بالانضمام لهذه الغرفة
    // مثلاً، هل هو جزء من فريق معين أو لديه صلاحية خاصة.
    // حالياً، نسمح لجميع المستخدمين الموثقين بالانضمام
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
