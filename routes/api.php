<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Api\ExecutionController;





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

Route::post('/projects/{project}/save-code', [ProjectController::class, 'saveCode']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);


Route::post('/execute', [ExecutionController::class, 'execute'])
     ->middleware(['throttle:6,1']); 






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
});



