<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FriendRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageReadController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\GroupController;
// Get the currently authenticated user
Route::middleware('auth:sanctum')->get(
    '/user',
    [UserController::class, 'me']
);

Route::middleware('auth:sanctum')->post(
    '/device-tokens',
    [DeviceTokenController::class, 'store']
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

// Friends endpoint
Route::middleware('auth:sanctum')->post(
    '/friends/search',
    [FriendRequestController::class, 'searchUser']
);

Route::middleware('auth:sanctum')->post(
    '/friends/request',
    [FriendRequestController::class, 'sendRequest']
);

Route::middleware('auth:sanctum')->get(
    '/friends/get-friend-request',
    [FriendRequestController::class, 'getFriendRequests']
);

// to accept or reject request
Route::middleware('auth:sanctum')->post(
    '/friends/respond-request/{friendRequest}',
    [FriendRequestController::class, 'respondToRequest']
);


Route::middleware('auth:sanctum')->get(
    '/friends/get-friends',
    [FriendRequestController::class, 'getFriends']
);

// friend suggestions (random preview list, and the full paginated list)
Route::middleware('auth:sanctum')->get(
    '/friends/suggestions',
    [FriendRequestController::class, 'getSuggestions']
);

Route::middleware('auth:sanctum')->get(
    '/friends/suggestions/all',
    [FriendRequestController::class, 'getAllSuggestions']
);

// conversation endpoint

Route::middleware('auth:sanctum')->post(
    '/conversations/{conversation}/messages',
    [MessageController::class, 'send']
);

Route::middleware('auth:sanctum')->get(
    '/conversations/{conversation}/messages',
    [MessageController::class, 'index']
);
Route::middleware('auth:sanctum')->get(
    '/conversations/get-chat-list',
    [ConversationController::class, 'getChatList']
);
Route::middleware('auth:sanctum')->post(
    '/messages/mark-as-read',
    [MessageReadController::class, 'markAsRead']
);

Route::middleware('auth:sanctum')->get(
    '/messages/{message}/read-status',
    [MessageReadController::class, 'getReadStatus']
);

Route::middleware('auth:sanctum')->put(
    '/messages/{message}/edit-message',
    [MessageController::class, 'update']
);

Route::middleware('auth:sanctum')->delete(
    '/messages/{message}/delete-message',
    [MessageController::class, 'destroy']
);
Route::middleware('auth:sanctum')->group(function () {

    Route::post(
        '/groups',
        [GroupController::class, 'store']
    );

    Route::get(
        '/groups/{conversation}/members',
        [GroupController::class, 'members']
    );

    Route::post(
        '/groups/{conversation}/members',
        [GroupController::class, 'addMembers']
    );
});