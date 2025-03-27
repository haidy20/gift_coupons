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
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $provider = auth('api')->user();

        if (!$provider->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please verify your account first.',
            ], 403);
        }
        // Return the provider details and token
        return new ProvLoginResource($provider, $token, auth('api')->factory()->getTTL() * 60);
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
            'success' => true,
            'message' => 'Password reset code sent successfully',
            'data' => null
        ], 200);
    }

    public function verifyCode(ProvVerifyCodeRequest $request)
    {
        $user = UsersAccount::where('reset_code', $request->reset_code)->first();
        // إذا لم يتم العثور على المستخدم أو الكود غير صحيح
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
                'data' => null

            ], 400);
        }

        // مسح الكود بعد التحقق بنجاح
        $user->reset_code = null; // مسح الكود
        $user->save(); // حفظ التغييرات في قاعدة البيانات

        return response()->json([
            'success' => true,
            'message' => 'Verification successfully.',
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
            'success' => true,
            'message' => 'Password changed successfully!',
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
                'status' => 'error',
                'message' => 'Unauthorized. Provider access only.',
                'data' => null
            ], 403);
        }

        // التحقق مما إذا كان الحساب نشطًا
        if (!$provider->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is not active. Please verify your account.',
                'data' => null
            ], 403);
        }

        // التحقق من كلمة المرور الحالية
        if (!Hash::check($request->current_password, $provider->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect.',
                'data' => null
            ], 400);
        }

        // التحقق من أن كلمة المرور الجديدة مختلفة عن القديمة
        if (Hash::check($request->new_password, $provider->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'New password cannot be the same as the old one.',
                'data' => null
            ], 400);
        }

        // تحديث كلمة المرور (بدون `Hash::make()` لأن `bcrypt` يُطبق تلقائيًا في الموديل)
        $provider->update([
            'password' => $request->new_password
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully!',
            'data' => null
        ], 200);
    }

    public function profile()
    {
        // Get the authenticated user
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'user not found.',
                'data' => null,
            ], 404); // أو يمكنك استخدام 403
        }

        // Ensure the user's account is active (verified)
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please verify your account.',
                'data' => null,
            ], 403);
        }

        // Return the user's profile
        return response()->json([
            'success' => true,
            'message' => 'Profile successfully retrived',
            'data' => new ProvProfileResource($user),
        ], 200);
    }

    public function updateProfile(ProvUpdateProfileRequest $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => null,
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please verify your account.',
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
            'success' => true,
            'message' => 'Profile updated successfully!',
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
                'success' => false,
                'message' => 'You must be logged in to log out.',
                'data' => null
            ], 404);
        }

        // تسجيل الخروج من النظام
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
            'data' => null
        ]);
    }
}
