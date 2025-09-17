<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // This was missing!
use Illuminate\Support\Facades\Log;  // This was missing!
use App\Http\Controllers\Controller;

class FileController extends Controller
{
    public function getProjectFiles($projectId)
    {
        try {
            // Check if user has access to this project
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

    // public function saveProjectFiles(Request $request, $projectId)
    // {
    //     try {
    //         // Validate request
    //         $request->validate([
    //             'files' => 'required|array',
    //             'files.*.name' => 'required|string',
    //             'files.*.content' => 'required|string',
    //             'files.*.id' => 'nullable|integer',
    //         ]);

    //         // Check project access
    //         $project = Project::where('id', $projectId)
    //             ->whereHas('members', function($query) {
    //                 $query->where('user_id', Auth::id());
    //             })
    //             ->first();

    //         if (!$project) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Project not found or access denied'
    //             ], 404);
    //         }

    //         $savedFiles = [];

    //         foreach ($request->input('files') as $fileData) { // Changed from $request->files to $request->input('files')
    //             if (isset($fileData['id']) && $fileData['id']) {
    //                 // Update existing file
    //                 $file = ProjectFile::where('id', $fileData['id'])
    //                     ->where('project_id', $projectId)
    //                     ->first();
                    
    //                 if ($file) {
    //                     $file->update([
    //                         'original_name' => $fileData['name'],
    //                         'content' => $fileData['content'],
    //                     ]);
    //                     $savedFiles[] = $file;
    //                 }
    //             } else {
    //                 // Create new file
    //                 $file = ProjectFile::create([
    //                     'project_id' => $projectId,
    //                     'original_name' => $fileData['name'],
    //                     'content' => $fileData['content'],
    //                 ]);
    //                 $savedFiles[] = $file;
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Files saved successfully',
    //             'files' => collect($savedFiles)->map(function($file) {
    //                 return [
    //                     'id' => $file->id,
    //                     'name' => $file->original_name,
    //                 ];
    //             })
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Error saving project files: ' . $e->getMessage());
    //         Log::error('Stack trace: ' . $e->getTraceAsString());
            
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server error occurred: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

//     public function saveProjectFiles(Request $request, $project)
// {
//     try {
//         // Validate request
//         $request->validate([
//             'files' => 'required|array',
//             'files.*.name' => 'required|string',
//             'files.*.content' => 'required|string',
//             'files.*.id' => 'nullable|integer',
//         ]);

//         // Get project ID from the injected model
//         $projectId = $project->id;

//         // Check project access (you can simplify this since $project is already injected)
//         $hasAccess = $project->members()->where('user_id', Auth::id())->exists();
        
//         if (!$hasAccess) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Project not found or access denied'
//             ], 404);
//         }

//         $savedFiles = [];

//         foreach ($request->input('files') as $fileData) {
//             if (isset($fileData['id']) && $fileData['id']) {
//                 // Update existing file
//                 $file = ProjectFile::where('id', $fileData['id'])
//                     ->where('project_id', $projectId)
//                     ->first();
                
//                 if ($file) {
//                     $file->update([
//                         'original_name' => $fileData['name'],
//                         'content' => $fileData['content'],
//                     ]);
//                     $savedFiles[] = $file;
//                 }
//             } else {
//                 // Create new file
//                 $file = ProjectFile::create([
//                     'project_id' => $projectId,
//                     'original_name' => $fileData['name'],
//                     'content' => $fileData['content'],
//                 ]);
//                 $savedFiles[] = $file;
//             }
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Files saved successfully',
//             'files' => collect($savedFiles)->map(function($file) {
//                 return [
//                     'id' => $file->id,
//                     'name' => $file->original_name,
//                 ];
//             })
//         ]);

//     } catch (\Exception $e) {
//         Log::error('Error saving project files: ' . $e->getMessage());
//         Log::error('Stack trace: ' . $e->getTraceAsString());
        
//         return response()->json([
//             'success' => false,
//             'message' => 'Server error occurred: ' . $e->getMessage()
//         ], 500);
//     }
// }
// public function saveProjectFiles(Request $request, Project $project)
// {
//     try {
//         // Validate request
//         $request->validate([
//             'files' => 'required|array',
//             'files.*.name' => 'required|string',
//             'files.*.content' => 'required|string',
//             'files.*.id' => 'nullable|integer',
//         ]);

//         // Check if user has access to this project
//         $hasAccess = $project->members()->where('user_id', Auth::id())->exists();
        
//         if (!$hasAccess) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Access denied'
//             ], 403);
//         }

//         $savedFiles = [];

//         foreach ($request->input('files') as $fileData) {
//             if (isset($fileData['id']) && $fileData['id']) {
//                 // Update existing file
//                 $file = ProjectFile::where('id', $fileData['id'])
//                     ->where('project_id', $project->id)
//                     ->first();
                
//                 if ($file) {
//                     $filePath = "project_files/{$project->id}/{$fileData['name']}";
                    
//                     // Update file on disk
//                     Storage::disk('public')->put($filePath, $fileData['content']);
                    
//                     $file->update([
//                         'original_name' => $fileData['name'],
//                         'content' => $fileData['content'],
//                         'file_path' => $filePath,
//                     ]);
//                     $savedFiles[] = $file;
//                 }
//             } else {
//                 // Create new file
//                 $filePath = "project_files/{$project->id}/{$fileData['name']}";
                
//                 // Store file on disk
//                 Storage::disk('public')->put($filePath, $fileData['content']);
                
//                 $file = ProjectFile::create([
//                     'project_id' => $project->id,
//                     'original_name' => $fileData['name'],
//                     'content' => $fileData['content'],
//                     'file_path' => $filePath,
//                 ]);
//                 $savedFiles[] = $file;
//             }
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Files saved successfully',
//             'files' => collect($savedFiles)->map(function($file) {
//                 return [
//                     'id' => $file->id,
//                     'name' => $file->original_name,
//                 ];
//             })
//         ]);

//     } catch (\Exception $e) {
//         Log::error('Error saving project files: ' . $e->getMessage());
        
//         return response()->json([
//             'success' => false,
//             'message' => 'Server error occurred: ' . $e->getMessage()
//         ], 500);
//     }
// }

public function saveProjectFiles(Request $request, $projectId)
{
    // ... (Your validation and project access check code here)

    // Prepare the data for upsert
    $filesData = collect($request->input('files'))->map(function($fileData) use ($projectId) {
    // Check if it's an existing file or a new one
    $existingFile = null;
    if (isset($fileData['id']) && is_numeric($fileData['id'])) {
        $existingFile = ProjectFile::find($fileData['id']);
    }

    $filePath = $existingFile ? 
        $existingFile->file_path : 
        "project_files/{$projectId}/" . strtolower(str_replace(' ', '_', $fileData['name']));

    return [
        'id' => $existingFile ? $existingFile->id : null,
        'project_id' => $projectId,
        'original_name' => $fileData['name'],
        'content' => $fileData['content'],
        'file_path' => $filePath, // ✅ Add the file_path here
    ];
})->toArray();

// Use upsert to handle both updates and creations in one query
ProjectFile::upsert(
    $filesData,
    ['project_id', 'original_name', 'id'], // Unique by id and project_id
    ['original_name', 'content', 'file_path'] 
);

    // After upsert, you can fetch the updated files
    $savedFiles = ProjectFile::where('project_id', $projectId)->get();

    return response()->json([
        'success' => true,
        'message' => 'Files saved successfully',
        'files' => $savedFiles->map(function($file) {
            return [
                'id' => $file->id,
                'name' => $file->original_name,
            ];
        })
    ]);
}
    // public function store(Request $request, Project $project)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'files.*' => 'required|file|max:5120',
    //         'project_title' => 'string|max:255',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     $savedFiles = [];

    //     if ($request->hasFile('files')) {
    //         foreach ($request->file('files') as $file) {
    //             $originalName = $file->getClientOriginalName();
    //             $content = file_get_contents($file->getPathname());
    //             $filePath = "project_files/{$project->id}/{$originalName}";

    //             // Store file on disk
    //             Storage::disk('public')->put($filePath, $content);

    //             // Save or update file in database with content
    //             $projectFile = ProjectFile::updateOrCreate(
    //                 [
    //                     'project_id' => $project->id,
    //                     'original_name' => $originalName
    //                 ],
    //                 [
    //                     'file_path' => $filePath,
    //                     'content' => $content,
    //                 ]
    //             );

    //             $savedFiles[] = $projectFile;
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Files saved successfully!',
    //         'files' => $savedFiles,
    //         'project' => $project
    //     ], 201);
    // }
public function store(Request $request, Project $project)
    {
        try {
            // Check if user has access to this project
            // $hasAccess = $project->members()->where('user_id', Auth::id())->exists();
            
            // if (!$hasAccess) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Access denied'
            //     ], 403);
            // }

            // $validator = Validator::make($request->all(), [
            //     'files.*' => 'required|file|max:5120', // 5MB max per file
            //     'files' => 'required|array|max:10', // Maximum 10 files at once
            // ]);

            // if ($validator->fails()) {
            //     return response()->json([
            //         'success' => false,
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            $savedFiles = [];

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $content = file_get_contents($file->getPathname());
                    $filePath = "project_files/{$project->id}/{$originalName}";

                    // Create project directory if it doesn't exist
                    $projectDir = "project_files/{$project->id}";
                    if (!Storage::disk('public')->exists($projectDir)) {
                        Storage::disk('public')->makeDirectory($projectDir);
                    }

                    // Store file on disk
                    Storage::disk('public')->put($filePath, $content);

                    // Save or update file in database with content
                    $projectFile = ProjectFile::updateOrCreate(
                        [
                            'project_id' => $project->id,
                            'original_name' => $originalName
                        ],
                        [
                            'file_path' => $filePath,
                            'content' => $content,
                        ]
                    );

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
    public function show(Project $project)
    {
        $files = $project->files()->get();
        
        return response()->json([
            'project' => $project,
            'files' => $files
        ]);
    }

    public function saveProject(Request $request, Project $project)
    {
        $validatedData = $request->validate([
            'projectData' => 'required|array',
            'projectData.id' => 'required',
            'projectData.title' => 'required|string',
            'projectData.files' => 'required|array',
            'projectData.files.*.name' => 'required|string',
            'projectData.files.*.content' => 'required|string',
        ]);

        // Update project title if provided
        if (isset($validatedData['projectData']['title'])) {
            $project->update(['title' => $validatedData['projectData']['title']]);
        }

        // Delete existing files for this project to handle file deletions from the frontend
        $existingFiles = ProjectFile::where('project_id', $project->id)->get();
        
        // Delete files from storage
        foreach ($existingFiles as $existingFile) {
            if (Storage::disk('public')->exists($existingFile->file_path)) {
                Storage::disk('public')->delete($existingFile->file_path);
            }
        }
        
        // Delete from database
        ProjectFile::where('project_id', $project->id)->delete();

        // Create project directory if it doesn't exist
        $projectDir = "project_files/{$project->id}";
        if (!Storage::disk('public')->exists($projectDir)) {
            Storage::disk('public')->makeDirectory($projectDir);
        }

        // Loop through each file from the request and save it
        foreach ($validatedData['projectData']['files'] as $fileData) {
            $filePath = "project_files/{$project->id}/{$fileData['name']}";
            
            // Store file content on disk
            Storage::disk('public')->put($filePath, $fileData['content']);
            
            // Store file info and content in database
            ProjectFile::create([
                'project_id' => $project->id,
                'file_path' => $filePath,
                'original_name' => $fileData['name'],
                'content' => $fileData['content'],
            ]);
        }

        return response()->json([
            'message' => 'Project saved successfully!',
            'project' => $project->fresh()
        ], 200);
    }

    // public function destroy(Project $project, ProjectFile $file)
    // {
    //     // Ensure the file belongs to the project
    //     if ($file->project_id !== $project->id) {
    //         return response()->json(['error' => 'File not found'], 404);
    //     }

    //     // Delete file from storage
    //     if (Storage::disk('public')->exists($file->file_path)) {
    //         Storage::disk('public')->delete($file->file_path);
    //     }

    //     // Delete from database
    //     $file->delete();

    //     return response()->json(['message' => 'File deleted successfully'], 200);
    // }
    // public function destroy(Project $project, ProjectFile $file)
    // {
    //     // Ensure the file belongs to the project
    //     if ($file->project_id !== $project->id) {
    //         return response()->json(['error' => 'File not found'], 404);
    //     }

    //     try {
    //         // Delete file from storage
    //         if (Storage::disk('public')->exists($file->file_path)) {
    //             Storage::disk('public')->delete($file->file_path);
    //         }

    //         // Delete from database
    //         $file->delete();

    //         return response()->json(['message' => 'File deleted successfully'], 200);
    //     } catch (\Exception $e) {
    //         Log::error('Error deleting file: ' . $e->getMessage());
    //         Log::error('Stack trace: ' . $e->getTraceAsString());

    //         return response()->json(['error' => 'Failed to delete file'], 500);
    //     }
    // }
    public function destroy(Project $project, ProjectFile $file)
    {
        try {
            // Check if user has access to this project
            $hasAccess = $project->members()->where('user_id', Auth::id())->exists();
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // Ensure the file belongs to the project
            if ($file->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found in this project'
                ], 404);
            }

            $fileName = $file->original_name;

            // Delete file from storage if it exists
            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            // Delete from database
            $file->delete();

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

    // Delete multiple files
    public function destroyMultiple(Request $request, Project $project)
    {
        try {
            // Check if user has access to this project
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

            // Get files that belong to this project
            $files = ProjectFile::where('project_id', $project->id)
                               ->whereIn('id', $fileIds)
                               ->get();

            foreach ($files as $file) {
                $fileName = $file->original_name;

                // Delete from storage
                if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }

                // Delete from database
                $file->delete();
                
                $deletedFiles[] = $fileName;
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

}