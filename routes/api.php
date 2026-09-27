<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Get the currently authenticated user
Route::middleware('auth:sanctum')->get(
    '/user',
    [UserController::class, 'me']
);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/verify-pin', [AuthController::class, 'verifyPin']);
Route::middleware('auth:sanctum')->post(
    '/auth/logout',
    [AuthController::class, 'logout']
);
Route::post('/upload/image', [UploadController::class, 'image']);

Route::middleware('auth:sanctum')->put(
    '/user/update-profile',
    [UserController::class, 'update']
);