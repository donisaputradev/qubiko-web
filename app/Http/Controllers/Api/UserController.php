<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseFormatter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Successfully get profile!');
    }


    public function personal(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'gender' => 'nullable|in:male,female',
                'birth' => 'nullable|date',
            ];

            if ($request->phone && $request->phone != $request->user()->phone) {
                $rules['phone'] = 'required|string|min:10|unique:users';
            }

            if ($request->email && $request->email != $request->user()->email) {
                $rules['email'] = 'required|email|min:10|unique:users';
            }

            if ($request->image) {
                $rules['image'] = 'required|image|mimes:jpeg,png,jpg,webp,svg|max:800';
            }

            $validated = $request->validate($rules);

            if ($request->file('image')) {
                $url = $request->user()->image;
                if ($url) {
                    Storage::delete($url);
                }

                $validated['image'] = $request->file('image')->store('users');
            }

            $user = User::where('email', $request->user()->email)->first();

            $user->update($validated);

            return ResponseFormatter::success($user, 'Successfully update personal!');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(null, $th->getMessage());
        }
    }

    public function change_password(Request $request)
    {
        try {
            $validated = $request->validate([
                'old_password' => 'required|string|min:6',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required|string|min:6',
            ]);

            $user = $request->user();

            if (!Hash::check($validated['old_password'], $user->password)) {
                return ResponseFormatter::error(false, 'Old password does not match!');
            }

            if ($validated['password'] == $validated['old_password']) {
                return ResponseFormatter::error(false, 'The password cannot be the same as the old password!');
            }

            $user->password = Hash::make($validated['password']);
            $user->save();

            return ResponseFormatter::success(true, 'Password successfully updated!');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(false, $th->getMessage());
        }
    }
}
