<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        // ১. আগে ইমেইল ও পাসওয়ার্ড ঠিক আছে কিনা চেক করুন
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $user = Auth::user(); // সরাসরি অথেনটিকেটেড ইউজার অবজেক্ট নিন

            // ২. রোল চেক করুন এবং স্পেসিফিক মেসেজ দিন
            if ($user->role === 'admin') {
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => 200,
                    'message' => 'Admin login successfully',
                    'token' => $token,
                    'id' => $user->id,
                    'name' => $user->name,
                ]);
            }

            // রোল admin না হলে ৪MD (Forbidden) বা ৪০১ দিন, কিন্তু মেসেজ আলাদা করুন
            return response()->json([
                'status' => 403,
                'message' => 'Access Denied: You do not have administrator privileges.',
            ], 403);

        }

        // ক্রেডিশিয়াল ভুল হলে আলাদা মেসেজ
        return response()->json([
            'status' => 401,
            'message' => 'Invalid email or password.'
        ], 401);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        // এখানে আপনি হার্ডকোডেড admin দিচ্ছেন, তাই এই ফর্ম দিয়ে তৈরি করলে ইউজার admin-ই হবে
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 201,
            'message' => 'Admin registered successfully',
            'token' => $token,
            'id' => $user->id,
            'name' => $user->name,
        ], 201);
    }
}
