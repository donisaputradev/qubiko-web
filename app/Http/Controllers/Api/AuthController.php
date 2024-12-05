<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseFormatter;
use App\Models\User;
use App\Otp\ResetPasswordOtp;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Str;
use SadiqSalau\LaravelOtp\Facades\Otp;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'phone' => 'nullable|string|min:10|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create(array_merge(
                $validatedData,
                ['password' => bcrypt($request->password)]
            ));

            $token = $user->createToken('auth_token')->plainTextToken;

            return ResponseFormatter::success(
                [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $user,
                ],
                'Authentication successfully',
            );
        } catch (\Throwable $th) {
            return ResponseFormatter::error(null, $th->getMessage(), 422);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if ($user) {
                if ($user->password) {
                    if (!$user || !Hash::check($request->password, $user->password)) {
                        return ResponseFormatter::error(null, 'Unauthorized', 401);
                    }

                    $token = $user->createToken('auth_token')->plainTextToken;

                    return ResponseFormatter::success(
                        [
                            'access_token' => $token,
                            'token_type' => 'Bearer',
                            'user' => $user,
                        ],
                        'Authentication successfully',
                    );
                } else {
                    return ResponseFormatter::error(null, 'Please login via google and setup password', 401);
                }
            } else {
                return ResponseFormatter::error(null, 'Unauthorized', 401);
            }
        } catch (\Throwable $th) {
            return ResponseFormatter::error(null, $th->getMessage(), 500);
        }
    }

    public function firebase(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email',
                'firebase_uid' => 'required|string',
            ]);

            $auth = Firebase::auth();

            $userGoogle = $auth->getUser($validatedData['firebase_uid']);

            if ($userGoogle) {
                $user = User::where('email', $validatedData['email'])->first();

                if (!$user) {
                    $user = User::create([
                        'name' => $validatedData['name'],
                        'email' => $validatedData['email'],
                        'firebase_uid' => $validatedData['firebase_uid'],
                    ]);
                }

                if (!$user->firebase_uid) {
                    $user->update([
                        'firebase_uid' => $validatedData['firebase_uid'],
                    ]);
                }

                $token = $user->createToken('auth_token')->plainTextToken;

                return ResponseFormatter::success(
                    [
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                        'user' => $user,
                    ],
                    'Authentication successfully',
                );
            } else {
                return ResponseFormatter::error(null, 'Unauthorized', 401);
            }
        } catch (\Throwable $th) {
            return ResponseFormatter::error(null, $th->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return ResponseFormatter::success(
                true,
                'User successfully logout',
            );
        } catch (\Throwable $th) {
            return ResponseFormatter::error(null, $th->getMessage(), 500);
        }
    }

    public function password(Request $request)
    {
        try {
            $user = auth('api')->user();
            if ($user) {
                $validatedData = $request->validate([
                    'old_password' => 'required|string|min:6',
                    'password' => 'required|string|min:6',
                ]);

                if (!Hash::check($validatedData['old_password'], $user->password)) {
                    return ResponseFormatter::error(null, 'Password lama tidak cocok', 422);
                }

                if ($validatedData['old_password'] == $validatedData['password']) {
                    return ResponseFormatter::error(null, 'Password harus berbeda dengan password lama', 404);
                }

                User::where('id', $user->id)->update(['password' => bcrypt($request->password)]);

                return ResponseFormatter::success(
                    null,
                    'Password successfully updated',
                );
            } else {
                return ResponseFormatter::error(null, 'Unauthorized', 401);
            }
        } catch (\Throwable $th) {
            return ResponseFormatter::error(null, $th->getMessage(), 500);
        }
    }

    public function reset_password(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users']);

        $otp = Otp::identifier($request->email)->send(
            new ResetPasswordOtp(email: $request->email),
            Notification::route('mail', $request->email)
        );

        if ($otp['status'] == Otp::OTP_SENT) {
            return ResponseFormatter::success(true, 'OTP sent successfully! Please check the email.');
        } else {
            return ResponseFormatter::error(false, 'OTP not sent! Please try again.');
        }
    }

    public function resend_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $otp = Otp::identifier($request->email)->update();

        if ($otp['status'] == Otp::OTP_SENT) {
            return ResponseFormatter::success(true, 'OTP resent successfully! Please check the email.');
        } else {
            return ResponseFormatter::error(false, 'OTP not sent! Please try again.');
        }
    }

    public function verify_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|min:6'
        ]);

        $otp = Otp::identifier($request->email)->check($request->otp);

        if ($otp['status'] == Otp::OTP_MATCHED) {
            return ResponseFormatter::success(true, 'OTP verified successfully! Please enter a new password.');
        } else {
            return ResponseFormatter::error(false, 'Invalid OTP code! Please try again.');
        }
    }

    public function new_password(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|min:6',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        $otp = Otp::identifier($request->email)->attempt($request->otp);

        if ($otp['status'] == Otp::OTP_PROCESSED) {
            $user = User::where('email', $request->email)->first();

            $user->update(['password' => bcrypt($request->password)]);

            return ResponseFormatter::success(true, 'Password changed successfully! Please login to continue.');
        } else {
            return ResponseFormatter::error(false, 'Invalid OTP code!');
        }
    }
}
