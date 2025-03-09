<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UsersAccount;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

// Requests
use App\Http\Requests\Users\UserRegisterRequest;
use App\Http\Requests\Users\UserLoginRequest;
use App\Http\Requests\Users\UserSendCodeRequest;
use App\Http\Requests\Users\UserVerifyCodeRequest;

use App\Http\Requests\Users\SendVerificationCodeRequest;
use App\Http\Requests\Users\VerifyCodeRequest;
use App\Http\Requests\Users\ResetPasswordRequest;
use App\Http\Requests\Users\UserChangePassRequest;





// Responses
use App\Http\Resources\Users\UserRegisterResource;
use App\Http\Resources\Users\UserLoginResource;




class UserAuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        // تأكد أن المستخدم وافق على الشروط
        if (!$request->has('agree_terms') || $request->agree_terms !== true) {
            return response()->json([
                'success' => false,
                'message' => 'You must agree to the terms and conditions.',
                'data' => null
            ], 422);
        }
        // Get the country code from the countries_codes table
        $country = Country::find($request->countries_id);

        // Validate phone number against the regex of the selected country
        if (!preg_match("/{$country->phone_regex}/", $request->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'The phone number does not match the selected country.',
                'data' => null
            ], 422);
        }
        $role = $request->role ?? 'client';
        $validatedData = $request->validated();
        $reset_code = 1111;

        $user = UsersAccount::create($validatedData + ['reset_code' => $reset_code, 'role' => $role, 'is_active' => false]);

        // Generate the JWT token after creation
        $token = JWTAuth::fromUser($user); // Use fromUser method to generate token

        // Return the user along with the country_code explicitly
        return new UserRegisterResource($user, $token, auth('api')->factory()->getTTL() * 60);
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = $request->credentials();

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth('api')->user();

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please verify your account first.',
            ], 403);
        }
        return new UserLoginResource($user, $token, auth('api')->factory()->getTTL() * 60);
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
            ], 401);
        }

        // تسجيل الخروج من النظام
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
            'data' => null
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = auth('api')->user(); // جلب المستخدم المسجل

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated.',
            ], 401);
        }

        // حذف المستخدم وجميع البيانات المرتبطة به
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your account has been deleted successfully.',
        ], 200);
    }


    //Steps of verfiy account (2 methods)
    public function resendResetCode(UserSendCodeRequest $request)
    {
        // Find the user by phone number
        $user = UsersAccount::where('phone', $request->phone)->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found.',
            ], 422);
        }

        // Use a fixed code "1111" as requested
        $resetCode = '1111';

        // Store the reset code in the database
        $user->reset_code = $resetCode;
        $user->save();

        // Simulate sending the code via SMS (e.g., Twilio)
        // Twilio::sendCode($user->phone, $resetCode);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to your phone.',
        ], 200);
    }

    public function verifyResetCode(UserVerifyCodeRequest $request)
    {

        // Find the user by reset code
        $user = UsersAccount::where('phone', $request->phone)->where('reset_code', $request->reset_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
            ], 422);
        }

        // Activate the user's account
        $user->is_active = 1;
        $user->reset_code = null; // Clear reset code
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Account verified successfully.',
        ], 200);
    }

    //Steps of forget password (3 methods)
    public function sendVerificationCode(SendVerificationCodeRequest $request)
    {
        $phone = $request->phone;
        // التحقق من وجود كود سابق في جدول users_account
        //    $user = DB::table('users_account')->where('phone', $phone)->first();
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

    public function verifyCode(VerifyCodeRequest $request)
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


    public function resetPassword(ResetPasswordRequest $request)
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

    public function changePassword(UserChangePassRequest $request)
{
    $user = auth('api')->user();

    // التحقق من دور المستخدم
    if ($user->role !== 'client') {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized. client access only.',
            'data' => null
        ], 403);
    }

    // التحقق مما إذا كان الحساب نشطًا
    if (!$user->is_active) {
        return response()->json([
            'status' => 'error',
            'message' => 'Your account is not active. Please verify your account.',
            'data' => null
        ], 403);
    }

    // التحقق من كلمة المرور الحالية
    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Current password is incorrect.',
            'data' => null
        ], 400);
    }

    // التحقق من أن كلمة المرور الجديدة مختلفة عن القديمة
    if (Hash::check($request->new_password, $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'New password cannot be the same as the old one.',
            'data' => null
        ], 400);
    }

    // تحديث كلمة المرور
    $user->update([
        'password' => $request->new_password
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Password changed successfully!',
        'data' => null
    ], 200);
}

}
