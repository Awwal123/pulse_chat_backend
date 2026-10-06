<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    use HttpResponses;

public function store(Request $request)
{
    $request->validate([
        'token' => ['required', 'string', 'max:500'],
    ]);

    $user = $request->user();

    // Remove old tokens belonging to this user.
    $user->deviceTokens()
        ->where('token', '!=', $request->token)
        ->delete();

    // Save the current token.
    $deviceToken = DeviceToken::updateOrCreate(
        [
            'token' => $request->token,
        ],
        [
            'user_id' => $user->id,
        ]
    );

    return $this->success(
        $deviceToken,
        'Device token saved successfully.'
    );
}
}