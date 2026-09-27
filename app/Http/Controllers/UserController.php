<?php

namespace App\Http\Controllers;

use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateProfileRequest;

class UserController extends Controller
{
    use HttpResponses;

    public function me(Request $request)
    {
        return $this->success(
            $request->user(),
            'User profile retrieved successfully.'
        );
    }

    public function update(UpdateProfileRequest $request)
{
    $user = $request->user();

    $user->update($request->validated());

    return $this->success(
        $user->fresh(),
        'Profile updated successfully.'
    );
}
}