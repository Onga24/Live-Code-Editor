<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Events\FileUpdated;
use App\Events\FileDeleted;
use App\Events\MultipleFilesDeleted;
use Carbon\Carbon;

class FileController extends Controller
{
    /**
     * ✅ دالة التحديث الفوري للملفات - FIXED
     */
    public function updateFileContent(Request $request, Project $project, ProjectFile $file): JsonResponse
    {
        try {
            // التحقق من صلاحيات المستخدم
            if (!$project->members()->where('user_id', auth()->id())->exists()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'content' => 'required|string',
            ]);

            // تحديث محتوى الملف
            $file->content = $request->content;
            $file->updated_by = auth()->id();
            $file->save();

            // ✅ بث التحديث لجميع أعضاء المشروع
            broadcast(new FileUpdated($file, auth()->id()))->toOthers();

            Log::info('File updated in real-time', [
                'file_id' => $file->id,
                'project_id' => $project->id,
                'updated_by' => auth()->id(),
                'content_length' => strlen($request->content)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File updated successfully',
                'file' => [
                    'id' => $file->id,
                    'content' => $file->content,
                    'updated_at' => $file->updated_at,
                    'updated_by' => auth()->id()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('File update error: ' . $e->getMessage(), [
                'file_id' => $file->id ?? 'unknown',
                'project_id' => $project->id ?? 'unknown',
                'user_id' => auth()->id(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update file'], 500);
        }
    }

    public function getProjectFiles($projectId)
    {
        try {
            $project = Project::where('id', $projectId)
                ->whereHas('members', function($query) {
                    $query->where('user_id', Auth::id());
                })
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or access denied'
                ], 404);
            }

            $files = ProjectFile::where('project_id', $projectId)->get();

            return response()->json([
                'success' => true,
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                ],
                'files' => $files->map(function($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->original_name,
                        'content' => $file->content,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading project files: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveProjectFiles(Request $request, $projectId)
    {
        try {
            $project = Project::where('id', $projectId)
                ->whereHas('members', function($query) {
                    $query->where('user_id', Auth::id());
                })
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or access denied'
                ], 404);
            }

            $request->validate([
                'files' => 'required|array',
                'files.*.name' => 'required|string',
                'files.*.content' => 'required|string',
                'files.*.id' => 'nullable|integer'
            ]);

            $savedFiles = [];
            $userId = Auth::id();

            foreach ($request->input('files') as $fileData) {
                $existingFile = null;
                
                if (isset($fileData['id']) && is_numeric($fileData['id'])) {
                    $existingFile = ProjectFile::where('project_id', $projectId)
                                             ->where('id', $fileData['id'])
                                             ->first();
                }

                $filePath = $existingFile ? 
                    $existingFile->file_path : 
                    "project_files/{$projectId}/" . strtolower(str_replace(' ', '_', $fileData['name']));

                $projectFile = ProjectFile::updateOrCreate(
                    [
                        'project_id' => $projectId,
                        'id' => $existingFile ? $existingFile->id : null,
                        'original_name' => $fileData['name']
                    ],
                    [
                        'content' => $fileData['content'],
                        'file_path' => $filePath,
                        'updated_by' => $userId
                    ]
                );

                // ✅ بث التحديث فوراً بعد الحفظ
                broadcast(new FileUpdated($projectFile, $userId))->toOthers();

                $savedFiles[] = [
                    'id' => $projectFile->id,
                    'name' => $projectFile->original_name,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Files saved successfully',
                'files' => $savedFiles
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving project files: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, Project $project)
    {
        try {
            $savedFiles = [];
            $userId = Auth::id();

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $content = file_get_contents($file->getPathname());
                    $filePath = "project_files/{$project->id}/{$originalName}";

                    $projectDir = "project_files/{$project->id}";
                    if (!Storage::disk('public')->exists($projectDir)) {
                        Storage::disk('public')->makeDirectory($projectDir);
                    }

                    Storage::disk('public')->put($filePath, $content);

                    $projectFile = ProjectFile::updateOrCreate(
                        [
                            'project_id' => $project->id,
                            'original_name' => $originalName
                        ],
                        [
                            'file_path' => $filePath,
                            'content' => $content,
                            'updated_by' => $userId
                        ]
                    );

                    // ✅ بث التحديث
                    broadcast(new FileUpdated($projectFile, $userId))->toOthers();

                    $savedFiles[] = [
                        'id' => $projectFile->id,
                        'name' => $projectFile->original_name,
                        'path' => $projectFile->file_path,
                        'size' => strlen($content)
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($savedFiles) . ' file(s) uploaded successfully!',
                'files' => $savedFiles,
                'project' => $project
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error uploading files: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Project $project, ProjectFile $file)
    {
        try {
            $hasAccess = $project->members()->where('user_id', Auth::id())->exists();
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            if ($file->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found in this project'
                ], 404);
            }

            $fileName = $file->original_name;
            $fileId = $file->id;
            $userId = Auth::id();

            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            $file->delete();

            // ✅ بث حدث حذف الملف
            broadcast(new FileDeleted($project->id, $fileId, $fileName, $userId))->toOthers();

            return response()->json([
                'success' => true,
                'message' => "File '{$fileName}' deleted successfully"
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting file: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyMultiple(Request $request, Project $project)
    {
        try {
            $hasAccess = $project->members()->where('user_id', Auth::id())->exists();
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $request->validate([
                'file_ids' => 'required|array',
                'file_ids.*' => 'required|integer|exists:project_files,id'
            ]);

            $fileIds = $request->input('file_ids');
            $deletedFiles = [];
            $userId = Auth::id();

            $files = ProjectFile::where('project_id', $project->id)
                               ->whereIn('id', $fileIds)
                               ->get();

            foreach ($files as $file) {
                $fileName = $file->original_name;

                if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }

                $file->delete();
                $deletedFiles[] = ['id' => $file->id, 'name' => $fileName];
            }

            // ✅ بث حدث حذف ملفات متعددة
            if (!empty($deletedFiles)) {
                broadcast(new MultipleFilesDeleted($project->id, $deletedFiles, $userId))->toOthers();
            }

            return response()->json([
                'success' => true,
                'message' => count($deletedFiles) . ' file(s) deleted successfully',
                'deleted_files' => $deletedFiles
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting multiple files: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ✅ انضمام المستخدم لجلسة التحرير
     */
    public function joinSession(Request $request, Project $project)
    {
        try {
            $user = Auth::user();
            
            $hasAccess = $project->members()->where('user_id', $user->id)->exists();
            if (!$hasAccess) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $sessionKey = "project_session:{$project->id}:user:{$user->id}";
            Cache::put($sessionKey, [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'joined_at' => now(),
                'last_activity' => now()
            ], now()->addMinutes(30));

            Log::info('User joined project session', [
                'user_id' => $user->id,
                'project_id' => $project->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Joined session successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error joining session: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * ✅ مغادرة جلسة التحرير
     */
    public function leaveSession(Request $request, Project $project)
    {
        try {
            $user = Auth::user();
            
            $sessionKey = "project_session:{$project->id}:user:{$user->id}";
            Cache::forget($sessionKey);
            
            $typingKey = "typing:{$project->id}:user:{$user->id}";
            Cache::forget($typingKey);

            Log::info('User left project session', [
                'user_id' => $user->id,
                'project_id' => $project->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Left session successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error leaving session: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * ✅ الحصول على المستخدمين المتصلين حالياً
     */
    public function getOnlineUsers(Request $request, Project $project)
    {
        try {
            $user = Auth::user();
            
            $hasAccess = $project->members()->where('user_id', $user->id)->exists();
            if (!$hasAccess) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            // محاكاة الحصول على المستخدمين المتصلين (يمكن تحسينها لاحقاً)
            $onlineUsers = [];
            
            return response()->json([
                'success' => true,
                'online_users' => $onlineUsers,
                'count' => count($onlineUsers)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting online users: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * ✅ تحديث حالة الكتابة
     */
    public function updateTypingStatus(Request $request, Project $project)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'file_id' => 'required|integer',
                'is_typing' => 'required|boolean'
            ]);
            
            $fileId = $request->input('file_id');
            $isTyping = $request->input('is_typing');
            
            $typingKey = "typing:{$project->id}:file:{$fileId}:user:{$user->id}";
            
            if ($isTyping) {
                Cache::put($typingKey, [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'file_id' => $fileId,
                    'started_at' => now()
                ], now()->addSeconds(10));
                
                Log::info('User started typing', [
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'file_id' => $fileId
                ]);
            } else {
                Cache::forget($typingKey);
                
                Log::info('User stopped typing', [
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'file_id' => $fileId
                ]);
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error updating typing status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}