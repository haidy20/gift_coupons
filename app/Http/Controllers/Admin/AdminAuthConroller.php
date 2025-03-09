<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UsersAccount;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

// Requests
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\MakeRegisterRequest;



// Responses
use App\Http\Resources\Admin\AdminLoginResource;
use App\Http\Resources\Admin\MakeRegisterResource;






class AdminAuthConroller extends Controller
{
    public function login(AdminLoginRequest $request)
    {

        // البحث عن المستخدم بناءً على رقم الهاتف
        $user = UsersAccount::where('email', $request->email)->first();

        $credentials = $request->only('email', 'password');
        // محاولة تسجيل الدخول باستخدام attempt
        if (!$token = JWTAuth::attempt($credentials)) {
            // dd($credentials, Hash::make($request->password), $user->password, Hash::check($request->password, $user->password));

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
                'data' => null
            ], 401);
        }

        // التأكد من أن المستخدم هو مسؤول (admin)
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Only admins can log in.',
                'data' => null
            ], 403);
        }
        // توليد التوكن باستخدام JWT
        $token = auth('api')->login($user);
        $user->is_active = 1;
        $user->save();

        return response()->json([
            'message' => 'Admin Login successfully',
            'data' => new AdminLoginResource((object)[
                'token' => $token,
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ])
        ], 200);
    }

    public function logout()
    {
        // التحقق من المستخدم الحالي
        $user = auth('api')->user();

        // إذا لم يكن هناك مستخدم مسجل الدخول، يرجع رسالة خطأ
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must be logged in to log out.',
                'data' => null
            ], 401);
        }

        // تسجيل الخروج من النظام
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Admin successfully logged out',
            'data' => null
        ], 200);
    }

    // Admin create provider
    public function createProvider(MakeRegisterRequest $request)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can create providers.',
            ], 403);
        }

        $role = $request->role ?? 'provider';
        // $reset_code = 1111;

        $provider = UsersAccount::create(array_merge(
            $request->validated(),
            [
                // 'reset_code' => $reset_code,
                'is_active' => true, // الأدمن يمكنه تنشيط الحساب مباشرة
                'role' => $role,
            ]
        ));

        $token = JWTAuth::fromUser($provider); // Use fromUser method to generate token

        return response()->json(
            new MakeRegisterResource($provider, $token, auth('api')->factory()->getTTL() * 60),
        );
    }


    // Admin create user
    public function createUser(MakeRegisterRequest $request)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can create providers.',
            ], 403);
        }
        $role = $request->role ?? 'client';
        // $reset_code = 1111;

        $user = UsersAccount::create(array_merge(
            $request->validated(),
            [
                // 'reset_code' => $reset_code,
                'is_active' => true, // الأدمن يمكنه تنشيط الحساب مباشرة
                'role' => $role,
            ]
        ));

        $token = JWTAuth::fromUser($user); // Use fromUser method to generate token

        return response()->json(
            new MakeRegisterResource($user, $token, auth('api')->factory()->getTTL() * 60)
        );
    }
}
