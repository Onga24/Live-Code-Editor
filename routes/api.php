<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\CodeAssistance;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProjectController;




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::get('/my-profile', [AuthController::class, 'getMyProfile']);
    Route::get('/my', [AuthController::class, 'getMyProfile']);

    // Project routes
    Route::post('projects', [ProjectController::class, 'store']);
    Route::post('projects/join', [ProjectController::class, 'joinByInvite']);
    Route::get('projects', [ProjectController::class, 'myProjects']);
    Route::put('projects/{project}', [ProjectController::class, 'update']);
    // Route::patch('projects/{project}', [ProjectController::class, 'update']); // اختياري
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

    // Chat routes
    Route::get('/projects/{project}/messages', [ChatController::class, 'projectMessages']);
    Route::post('/projects/{project}/messages', [ChatController::class, 'storeProjectMessage']);
    Route::get('/messages', [ChatController::class, 'index']);
    Route::post('/messages', [ChatController::class, 'store']);
    Route::get('projects', [ProjectController::class, 'myprojects']);
    // Your existing routes...
    
    // Project file routes
    Route::get('/projects/{project}/files', [FileController::class, 'getProjectFiles']);
    Route::post('/projects/{project}/files', [FileController::class, 'saveProjectFiles']);
    // Route::post('/projects/{projectId}/files', [FileController::class, 'saveProjectFiles']);    
    // Alternative routes if needed

    Route::post('/projects/{project}/save', [FileController::class, 'saveProject']);
    Route::get('/projects/{project}/show', [FileController::class, 'show']);
    Route::post('/projects/{project}/files/upload', [FileController::class, 'store']);
    Route::delete('/projects/{project}/files/{file}', [FileController::class, 'destroy']);
    Route::delete('/projects/{project}/files', [FileController::class, 'destroyMultiple']);

    Route::post('/ai/code-assist', [CodeAssistance::class, 'codeAssist']);
});

// Broadcasting authentication route - يجب أن يكون منفصل وخارج middleware group
Route::middleware('auth:sanctum')->post('/broadcasting/auth', function (Request $request) {
    try {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        return Broadcast::auth($request);

    } catch (\Exception $e) {
        \Log::error('Broadcasting auth error: ' . $e->getMessage());
        return response()->json(['error' => 'Broadcasting auth failed'], 403);
    }
});


// OpenAI route
Route::post('/chat', function (Request $request) {
    try {
        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-4o-mini',
                'input' => $request->message,
            ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'OpenAI API failed',
                'details' => $response->json(),
            ], 500);
        }

        return $response->json();
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Server exception',
            'message' => $e->getMessage(),
        ], 500);
    }
});




Route::prefix('admin')->middleware(['auth:sanctum','is_admin'])->group(function () {
    // Users
    Route::get('users', [AdminUserController::class, 'index']);
    Route::get('users/{user}', [AdminUserController::class, 'show']);
    Route::put('users/{user}', [AdminUserController::class, 'update']); 
    Route::delete('users/{user}', [AdminUserController::class, 'destroy']); 
    Route::post('users/{id}/restore', [AdminUserController::class, 'restore']);
    Route::delete('users/{id}/force', [AdminUserController::class, 'forceDelete']);
    Route::get('dashboard', [AdminUserController::class, 'stats']);



    // Projects
    Route::get('projects', [AdminProjectController::class, 'index']);
    Route::get('projects/{project}', [AdminProjectController::class, 'show']);
    Route::put('projects/{project}', [AdminProjectController::class, 'update']);
    Route::delete('projects/{project}', [AdminProjectController::class, 'destroy']); 
    Route::post('projects/{id}/restore', [AdminProjectController::class, 'restore']);
    Route::delete('projects/{id}/force', [AdminProjectController::class, 'forceDelete']);



