<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Get the currently authenticated user
Route:: middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/verify-pin', [AuthController::class, 'verifyPin']);
Route::middleware('auth:sanctum')->post(
    '/auth/logout',
    [AuthController::class, 'logout']
);
Route::post('/upload/image', [UploadController::class, 'image']);

Route::post('/debug/test-upload', function (Request $request) {
    return response()->json([
        'files' => $_FILES,
        'has_file' => $request->hasFile('image'),
        'file' => $request->file('image'),
    ]);
});