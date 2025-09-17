<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\CodeAssistance;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

Route::post('login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);



Route::middleware('auth:sanctum')->group(function () {
   
Route::post('logout', [AuthController::class, 'logout']);
Route::post('/update-profile', [AuthController::class, 'updateProfile']);
Route::get('/my-profile',[AuthController::class,'getMyProfile']);
Route::get('/me', [AuthController::class, 'getMyProfile']);

Route::post('projects', [ProjectController::class, 'store']);
Route::post('projects/join', [ProjectController::class, 'joinByInvite']);

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


