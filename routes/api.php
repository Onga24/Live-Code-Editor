<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;



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

});


