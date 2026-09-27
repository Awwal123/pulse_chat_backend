<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendOtpRequest;
use App\Models\OtpVerification;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\VerifyPinRequest;
use App\Traits\HttpResponses;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
class AuthController extends Controller
{
    use HttpResponses;

  public function register(StoreUserRequest $request)
{
    $verifiedOtp = OtpVerification::where('phone', $request->phone)
        ->where('purpose', 'register')
        ->whereNotNull('verified_at')
        ->latest()
        ->first();

    if (!$verifiedOtp) {
        return $this->error(
            null,
            'Please verify your phone number first.',
            400
        );
    }

    $user = User::create([
        'phone' => $request->phone,
        'name' => $request->name,
        'profile_picture' => $request->profile_picture,
        'security_pin' => $request->security_pin,
    ]);

    $token = $user->createToken('pulse-chat')->plainTextToken;

    return $this->success(
        [
            'user' => $user,
            'token' => $token,
        ],
        'Account created successfully.',
        201
    );
}

  public function sendOtp(SendOtpRequest $request)
{
    $userExists = User::where('phone', $request->phone)->exists();

    if ($request->purpose === 'register' && $userExists) {
        return $this->error(
            null,
            'An account with this phone number already exists.',
            409
        );
    }

    if ($request->purpose === 'login' && !$userExists) {
        return $this->error(
            null,
            'No account found with this phone number.',
            404
        );
    }

    // Check if a recent OTP was already sent
    $recentOtp = OtpVerification::where('phone', $request->phone)
        ->where('purpose', $request->purpose)
        ->latest()
        ->first();

    if ($recentOtp && $recentOtp->created_at->gt(now()->subMinute())) {
        return $this->error(
            null,
            'Please wait before requesting another OTP.',
            429
        );
    }

    // Invalidate any previous unused OTP
    OtpVerification::where('phone', $request->phone)
        ->where('purpose', $request->purpose)
        ->whereNull('verified_at')
        ->update([
            'expires_at' => now(),
        ]);

    // Generate a new OTP
    $otp = random_int(1000, 9999);

    OtpVerification::create([
        'phone' => $request->phone,
        'otp' => $otp,
        'purpose' => $request->purpose,
        'expires_at' => now()->addMinutes(5),
    ]);

    return $this->success(
        null,
        'OTP sent successfully.'
    );
}

 public function verifyOtp(VerifyOtpRequest $request)
{
    $otpVerification = OtpVerification::where('phone', $request->phone)
        ->where('purpose', $request->purpose)
        ->whereNull('verified_at')
        ->latest()
        ->first();

    if (!$otpVerification) {
        return $this->error(
            null,
            'OTP not found or already verified.',
            404
        );
    }

    if ($otpVerification->expires_at->isPast()) {
        return $this->error(
            null,
            'OTP has expired.',
            400
        );
    }

    if ($otpVerification->otp !== $request->otp) {
        return $this->error(
            null,
            'Invalid OTP.',
            400
        );
    }

    $otpVerification->update([
        'verified_at' => now(),
    ]);

    // Registration OTP
    if ($request->purpose === 'register') {
        return $this->success(
            [
                'purpose' => 'register',
            ],
            'OTP verified successfully.'
        );
    }

    // Login OTP
    $user = User::where('phone', $request->phone)->first();

    if (!$user) {
        return $this->error(
            null,
            'User not found.',
            404
        );
    }

    // User has a security PIN
    if ($user->security_pin) {
        return $this->success(
            [
                'purpose' => 'login',
                'requires_pin' => true,
            ],
            'OTP verified. PIN required.'
        );
    }

    // User does not have a security PIN
    $token = $user->createToken('pulse-chat')->plainTextToken;

    return $this->success(
        [
            'purpose' => 'login',
            'requires_pin' => false,
            'user' => $user,
            'token' => $token,
        ],
        'Login successful.'
    );
}

   public function verifyPin(VerifyPinRequest $request)
{
    $user = User::where('phone', $request->phone)->first();

    if (!$user) {
        return $this->error(
            null,
            'User not found.',
            404
        );
    }

    if (!$user->security_pin) {
        return $this->error(
            null,
            'This account does not have a security PIN.',
            400
        );
    }

    if (!Hash::check($request->security_pin, $user->security_pin)) {
        return $this->error(
            null,
            'Invalid security PIN.',
            401
        );
    }

    $token = $user->createToken('pulse-chat')->plainTextToken;

    return $this->success(
        [
            'user' => $user,
            'token' => $token,
        ],
        'Login successful.'
    );
}

public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return $this->success(
        null,
        'Logged out successfully.'
    );
}
}