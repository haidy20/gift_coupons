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
use App\Http\Requests\Admin\AdminCreateAdminRequest;
use App\Http\Requests\Admin\AdminUpdateRequest;


// Responses
use App\Http\Resources\Admin\AdminLoginResource;
use App\Http\Resources\Admin\MakeRegisterResource;
use App\Http\Resources\Admin\AdminCreateAdminResource;
use App\Http\Resources\Admin\AdminMakeUsersResource;
use App\Http\Resources\Admin\AdminCrudsUsersResource;





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
        // if ($user->role !== 'superAdmin') {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Access denied. Only superAdmin can log in.',
        //         'data' => null
        //     ], 403);
        // }
        // توليد التوكن باستخدام JWT
        $token = auth('api')->login($user);
        $user->is_active = 1;
        $user->save();

        return response()->json([
            'success' => true,
            'message' =>  trans('message.sucess'),
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
            'message' => trans('sucess'),
            'data' => null
        ], 200);
    }

    ////////////////////////////// Admin create provider cruds/////////////////////////////////////////
    public function createProvider(MakeRegisterRequest $request)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only superAdmin can create providers.',
            ], 403);
        }

        $role = $request->role ?? 'provider';
        $provider = UsersAccount::create(array_merge(
            $request->validated(),
            [
                'is_active' => true, // الأدمن يمكنه تنشيط الحساب مباشرة
                'role' => $role,
            ]
        ));

        $token = JWTAuth::fromUser($provider); // Use fromUser method to generate token
        $expiresIn = auth('api')->factory()->getTTL() * 60; // مدة انتهاء التوكن

        return response()->json([
            'status' => true,
            'message' => 'Provider created successfully',
            'data' => new AdminMakeUsersResource($provider, $token, $expiresIn),
        ]);
    }

    public function showProvider($id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only superAdmin can show provider.',
                'data' => null
            ], 403);
        }
        $provider = UsersAccount::where('role', 'provider')->find($id);

        if (!$provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider not found.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Provider retrived successfully',
            'data' => new AdminCrudsUsersResource($provider),
        ]);
    }

    public function showAllProviders()
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only superAdmin can show providers.',
            ], 403);
        }
        $providers = UsersAccount::where('role', 'provider')->get();

        return response()->json([
            'status' => true,
            'message' => 'Providers retrived successfully',
            'data' =>  AdminCrudsUsersResource::collection($providers),
        ]);
    }


    public function updateProvider(AdminUpdateRequest $request, $id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only superAdmin can update providers.',
            ], 403);
        }
        $provider = UsersAccount::where('role', 'provider')->find($id);

        if (!$provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider not found.',
                'data' => null
            ], 404);
        }

        // تحديث الصورة مباشرة باستخدام setter
        if ($request->hasFile('image')) {
            $provider->image = $request->file('image'); // سيستدعي setImageAttribute تلقائيًا4
            // dd($provider->image);
        }

        // تحديث باقي بيانات المستخدم
        $provider->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'provider updated successfully',
            'data' => new AdminCrudsUsersResource($provider),
        ]);
    }


    public function deleteProvider($id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only superAdmin can update providers.',
            ], 403);
        }
        $provider = UsersAccount::where('role', 'provider')->find($id);

        if (!$provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider not found.',
                'data' => null
            ], 404);
        }
        $provider->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'provider deleted successfully',
            'data' => null
        ]);
    }

    ////////////////////////////// Admin create user cruds/////////////////////////////////////////
    public function createUser(MakeRegisterRequest $request)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only superAdmin can create providers.',
            ], 403);
        }
        $role = $request->role ?? 'client';
        $user = UsersAccount::create(array_merge(
            $request->validated(),
            [
                'is_active' => true, // الأدمن يمكنه تنشيط الحساب مباشرة
                'role' => $role,
            ]
        ));

        $token = JWTAuth::fromUser($user); // Use fromUser method to generate token

        return response()->json(
            new MakeRegisterResource($user, $token, auth('api')->factory()->getTTL() * 60)
        );
    }

    ////////////////////////////// Admin create admin cruds/////////////////////////////////////////
    public function createAdmin(AdminCreateAdminRequest $request)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only superAdmin can create another admins.',
                'data' => null,
            ], 403);
        }

        $admin = UsersAccount::create(array_merge(
            $request->validated(),
            [
                'is_active' => true, // الأدمن يمكنه تنشيط الحساب مباشرة
                'role' => 'admin',
            ]
        ));

        return response()->json([
            'status' => true,
            'message' => 'Admin created successfully',
            'data' => new AdminCreateAdminResource($admin),
        ], 200);
    }
}
