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
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.invalid_credentials'),
                'data' => null
            ], 401);
        }

        // التأكد من أن المستخدم هو مسؤول (admin)
        // if ($user->role !== 'superAdmin') {
        //     return response()->json([
        //         'status' => 'error',
        // 'message' => trans('messages.unauthorized_user'),
                // 'data' => null
        //     ], 403);
        // }
        // توليد التوكن باستخدام JWT
        $token = auth('api')->login($user);
        $user->is_active = 1;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' =>  trans('messages.login_successfully'),
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
                'status' => 'fail',
                'message' => trans('messages.must_login'),
                'data' => null
            ], 401);
        }

        // تسجيل الخروج من النظام
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.logout_successfully'),
            'data' => null
        ], 200);
    }

    ////////////////////////////// Admin create provider cruds/////////////////////////////////////////
    public function createProvider(MakeRegisterRequest $request)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data'=>null
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
            'status' => 'success',
            'message' => trans('messages.provider_created_successfully'),
            'data' => new AdminMakeUsersResource($provider, $token, $expiresIn),
        ]);
    }

    public function showProvider($id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        $provider = UsersAccount::where('role', 'provider')->find($id);

        if (!$provider) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.provider_not_found'),
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.provider_retrieved_successfully'),
            'data' => new AdminCrudsUsersResource($provider),
        ]);
    }

    public function showAllProviders()
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null

            ], 403);
        }
        $providers = UsersAccount::where('role', 'provider')->get();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.providers_retrieved_successfully'),
            'data' =>  AdminCrudsUsersResource::collection($providers),
        ]);
    }


    public function updateProvider(AdminUpdateRequest $request, $id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }
        $provider = UsersAccount::where('role', 'provider')->find($id);

        if (!$provider) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.provider_not_found'),
                'data' => null
            ], 404);
        }

        // تحديث الصورة مباشرة باستخدام setter
        if ($request->hasFile('image')) {
            $provider->image = $request->file('image'); // سيستدعي setImageAttribute تلقائيًا4
        }

        // تحديث باقي بيانات المستخدم
        $provider->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.provider_updated_successfully'),
            'data' => new AdminCrudsUsersResource($provider),
        ]);
    }


    public function deleteProvider($id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }
        $provider = UsersAccount::where('role', 'provider')->find($id);

        if (!$provider) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.provider_not_found'),
                'data' => null
            ], 404);
        }
        $provider->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.provider_deleted_successfully'),
            'data' => null
        ]);
    }

    ////////////////////////////// Admin create user cruds/////////////////////////////////////////
    public function createUser(MakeRegisterRequest $request)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
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
        $expiresIn = auth('api')->factory()->getTTL() * 60; // مدة انتهاء التوكن

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.user_created_successfully'),
            'data' => new AdminMakeUsersResource($user, $token, $expiresIn),
        ], 200);
    }

    public function updateUser(AdminUpdateRequest $request, $id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }
        $user = UsersAccount::where('role', 'client')->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.user_not_found'),
                'data' => null
            ], 404);
        }

        // تحديث الصورة مباشرة باستخدام setter
        if ($request->hasFile('image')) {
            $user->image = $request->file('image'); // سيستدعي setImageAttribute تلقائيًا4
        }

        // تحديث باقي بيانات المستخدم
        $user->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.user_updated_successfully'),
            'data' => new AdminCrudsUsersResource($user),
        ]);
    }

    public function showUser($id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        $user = UsersAccount::where('role', 'client')->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.user_not_found'),
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.user_retrieved_successfully'),
            
            'data' => new AdminCrudsUsersResource($user),
        ]);
    }

    public function showAllUsers()
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }
        $users = UsersAccount::where('role', 'client')->get();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.users_retrieved_successfully'),
            'data' =>  AdminCrudsUsersResource::collection($users),
        ]);
    }

    public function deleteUser($id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        $user = UsersAccount::where('role', 'client')->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.user_not_found'),
                'data' => null
            ], 404);
        }
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.user_deleted_successfully'),
            'data' => null
        ]);
    }

    ////////////////////////////// Admin create admin cruds/////////////////////////////////////////
    public function createAdmin(AdminCreateAdminRequest $request)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
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

        $token = JWTAuth::fromUser($admin); // Use fromUser method to generate token
        $expiresIn = auth('api')->factory()->getTTL() * 60; // مدة انتهاء التوكن

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.admin_created_successfully'),
            'data' => new AdminMakeUsersResource($admin, $token, $expiresIn),
        ], 200);
    }


    public function updateAdmin(AdminUpdateRequest $request, $id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data'=>null
            ], 403);
        }
        $admin = UsersAccount::where('role', 'admin')->find($id);

        if (!$admin) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.admin_not_found'),
                'data' => null
            ], 404);
        }

        // تحديث الصورة مباشرة باستخدام setter
        if ($request->hasFile('image')) {
            $admin->image = $request->file('image'); // سيستدعي setImageAttribute تلقائيًا4
            // dd($provider->image);
        }

        // تحديث باقي بيانات المستخدم
        $admin->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.admin_updated_successfully'),
            'data' => new AdminCrudsUsersResource($admin),
        ]);
    }

    public function showAdmin($id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        $admin = UsersAccount::where('role', 'admin')->find($id);

        if (!$admin) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.admin_not_found'),
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.admin_retrieved_successfully'),
            'data' => new AdminCrudsUsersResource($admin),
        ]);
    }

    public function showAllAdmins()
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data'=>null
            ], 403);
        }
        $admins = UsersAccount::where('role', 'admin')->get();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.admins_retrieved_successfully'),
            'data' =>  AdminCrudsUsersResource::collection($admins),
        ]);
    }

    public function deleteAdmin($id)
    {
        // تأكد أن المستخدم الحالي هو أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data'=>null
            ], 403);
        }
        $admin = UsersAccount::where('role', 'admin')->find($id);

        if (!$admin) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.admin_not_found'),
                'data' => null
            ], 404);
        }
        $admin->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.admin_deleted_successfully'),
            'data' => null
        ]);
    }
}
