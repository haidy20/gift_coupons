<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\UsersAccount;
use App\Models\Country;
use Tymon\JWTAuth\Facades\JWTAuth;
// Requests
use App\Http\Requests\Providers\ProvLoginRequest;
use App\Http\Requests\Providers\ProvSendVerificationCodeRequest;
use App\Http\Requests\Providers\ProvVerifyCodeRequest;
use App\Http\Requests\Providers\ProvResetPasswordRequest;
use App\Http\Requests\Providers\ProvChangePassRequest;
use App\Http\Requests\Providers\ProvUpdateProfileRequest;

// Resources
use App\Http\Resources\Providers\ProvLoginResource;
use App\Http\Resources\Providers\ProvProfileResource;
use App\Http\Resources\Providers\ProvUpdateProfileResource;

class ProviderAuthController extends Controller
{

    public function login(ProvLoginRequest $request)
    {

        $credentials = $request->credentials();

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.invalid_credentials'),
                'data' => null
            ], 401);
        }

        $provider = auth('api')->user();

        if (!$provider->is_active) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.your_account_not_active'),
                'data' => null
            ], 403);
        }
        // Return the provider details and token
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.login_successfully'),
            'data' => new ProvLoginResource($provider, $token, auth('api')->factory()->getTTL() * 60)
        ], 200);
    }

    //Steps of forget password (3 methods)
    public function sendVerificationCode(ProvSendVerificationCodeRequest $request)
    {
        $phone = $request->phone;
        $resetCode = '1111';

        // حفظ أو تحديث الكود في جدول users_account
        DB::table('users_accounts')->updateOrInsert(
            ['phone' => $phone],
            ['reset_code' => $resetCode, 'created_at' => now()] // التأكد من إضافة العمود password_reset_code
        );

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.password_reset_code_sent'),
            'data' => null
        ], 200);
    }

    public function verifyCode(ProvVerifyCodeRequest $request)
    {
        $user = UsersAccount::where('reset_code', $request->reset_code)->first();
        // إذا لم يتم العثور على المستخدم أو الكود غير صحيح
        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.invalid_verification_code'),
                'data' => null

            ], 400);
        }

        // مسح الكود بعد التحقق بنجاح
        $user->reset_code = null; // مسح الكود
        $user->save(); // حفظ التغييرات في قاعدة البيانات

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.verification_successful'),
            'data' => null

        ], 200);
    }

    public function resetPassword(ProvResetPasswordRequest $request)
    {
        // تحديث كلمة المرور في قاعدة البيانات
        DB::table('users_accounts')->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.password_changed'),
            'data' => null
        ], 200);
    }

    // Change Password 
    public function changePassword(ProvChangePassRequest $request)
    {
        $provider = auth('api')->user();

        // التحقق من دور المستخدم
        if ($provider->role !== 'provider') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        // التحقق مما إذا كان الحساب نشطًا
        if (!$provider->is_active) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.your_account_not_active'),
                'data' => null
            ], 403);
        }

        // التحقق من كلمة المرور الحالية
        if (!Hash::check($request->current_password, $provider->password)) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.current_password_incorrect'),
                'data' => null
            ], 400);
        }

        // التحقق من أن كلمة المرور الجديدة مختلفة عن القديمة
        if (Hash::check($request->new_password, $provider->password)) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.password_same_as_old'),
                'data' => null
            ], 400);
        }

        // تحديث كلمة المرور (بدون `Hash::make()` لأن `bcrypt` يُطبق تلقائيًا في الموديل)
        $provider->update([
            'password' => $request->new_password
        ]);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.password_changed'),
            'data' => null
        ], 200);
    }

    public function profile()
    {
        // Get the authenticated user
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.user_not_found'),
                'data' => null,
            ], 404); // أو يمكنك استخدام 403
        }

        // Ensure the user's account is active (verified)
        if (!$user->is_active) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.your_account_not_active'),
                'data' => null,
            ], 403);
        }

        // Return the user's profile
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.profile_retrived'),
            'data' => new ProvProfileResource($user),
        ], 200);
    }

    public function updateProfile(ProvUpdateProfileRequest $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.user_not_found'),
                'data' => null,
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.your_account_not_active'),
                'data' => null,
            ], 403);
        }

        // استخدام الـ setter لتحديث الصورة
        if ($request->hasFile('image')) {
            $user->image = $request->file('image');
        }


        // تحديث البيانات الأخرى
        $user->update($request->validated());
        // استرجاع البيانات مع الوسائط
        $userWithMedia = UsersAccount::with('media', 'country')->find($user->id);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.profile_updated'),
            'data' => new ProvUpdateProfileResource($userWithMedia),
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
            ], 404);
        }

        // تسجيل الخروج من النظام
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.logout_successfully'),
            'data' => null
        ]);
    }
}
