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
use App\Http\Requests\Users\UserUpdateProfileRequest;


// Responses
use App\Http\Resources\Users\UserRegisterResource;
use App\Http\Resources\Users\UserLoginResource;
use App\Http\Resources\Users\UserProfileResource;
use App\Http\Resources\Users\UserUpdateProfileResource;


class UserAuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        // تأكد أن المستخدم وافق على الشروط
        if (!$request->has('agree_terms') || $request->agree_terms !== true) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.agree_terms'),
                'data' => null
            ], 422);
        }
        // Get the country code from the countries_codes table
        $country = Country::find($request->countries_id);

        // Validate phone number against the regex of the selected country
        if (!preg_match("/{$country->phone_regex}/", $request->phone)) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.phone_country_mismatch'),
                'data' => null
            ], 422);
        }
        $role = $request->role ?? 'client';
        $validatedData = $request->validated();
        $reset_code = 1111;

        $user = UsersAccount::create($validatedData + ['reset_code' => $reset_code, 'role' => $role, 'is_active' => false]);

        // Generate the JWT token after creation
        $token = JWTAuth::fromUser($user); // Use fromUser method to generate token

        return response()->json([
            'status' => 200,
            'message' => trans('messages.registration_successfully'),
            'data' => new UserRegisterResource($user, $token, auth('api')->factory()->getTTL() * 60)
        ], 200);
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = $request->credentials();

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.invalid_credentials'),
                'data' => null
            ], 401);
        }

        $user = auth('api')->user();

        if (!$user->is_active) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.your_account_not_active'),
                'data' => null
            ], 403);
        }
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.login_successfully'),
            'data' => new UserLoginResource($user, $token, auth('api')->factory()->getTTL() * 60)
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
            'data' => new UserProfileResource($user),
        ], 200);
    }

    public function updateProfile(UserUpdateProfileRequest $request)
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
        // $user = UsersAccount::with('media')->find(auth()->id());

        // تحديث الصورة مباشرة باستخدام setter
        if ($request->hasFile('image')) {
            $user->image = $request->file('image'); // سيستدعي setImageAttribute تلقائيًا
        }

        // تحديث باقي بيانات المستخدم
        $user->update($request->validated());

        // استرجاع البيانات مع الوسائط
        $userWithMedia = UsersAccount::with('media', 'country')->find($user->id);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.profile_updated'),
            'data' => new UserUpdateProfileResource($userWithMedia),
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

    public function deleteAccount()
    {
        $user = auth('api')->user(); // جلب المستخدم المسجل

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 401);
        }

        // حذف المستخدم وجميع البيانات المرتبطة به
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.account_deleted'),
            'data' => null
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
                'status' => 'fail',
                'message' => trans('messages.phone_not_found'),
                'data' => null
            ], 422);
        }

        // Use a fixed code "1111" as requested
        $resetCode = '1111';

        // Store the reset code in the database
        $user->reset_code = $resetCode;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.verification_code_sent'),
            'data' => null
        ], 200);
    }

    public function verifyResetCode(UserVerifyCodeRequest $request)
    {

        // Find the user by reset code
        $user = UsersAccount::where('phone', $request->phone)->where('reset_code', $request->reset_code)->first();

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.invalid_verification_code'),
                'data' => null
            ], 422);
        }

        // Activate the user's account
        $user->is_active = 1;
        $user->reset_code = null; // Clear reset code
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.account_verified'),
            'data' => null
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
            'status' => 'success',
            'message' => trans('messages.password_reset_code_sent'),
            'data' => null
        ], 200);
    }

    public function verifyCode(VerifyCodeRequest $request)
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


    public function resetPassword(ResetPasswordRequest $request)
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
    public function changePassword(UserChangePassRequest $request)
    {
        $user = auth('api')->user();

        // التحقق من دور المستخدم
        if ($user->role !== 'client') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        // التحقق مما إذا كان الحساب نشطًا
        if (!$user->is_active) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.your_account_not_active'),
                'data' => null
            ], 403);
        }

        // التحقق من كلمة المرور الحالية
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.current_password_incorrect'),
                'data' => null
            ], 400);
        }

        // التحقق من أن كلمة المرور الجديدة مختلفة عن القديمة
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.password_same_as_old'),
                'data' => null
            ], 400);
        }

        // تحديث كلمة المرور
        $user->update([
            'password' => $request->new_password
        ]);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.password_changed'),
            'data' => null
        ], 200);
    }
}
