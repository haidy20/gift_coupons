<?php

namespace App\Http\Middleware;

use App\Models\UsersAccount;
use Closure;
use Illuminate\Support\Facades\Auth;

class CustomPermissionOldMiddleware
{
    public function handle($request, Closure $next, $permission = null, $guard = null)
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            $permission = $request->route()->getName();

            // جلب الأدوار مع الترجمة
            $role = $user->role; // اجلب الدور
            if ($role && method_exists($role, 'translations')) {
                $roleName = optional($role->translations()->where('locale', app()->getLocale())->first())->name;
            } else {
                // التعامل مع حالة عدم وجود دور أو علاقة
                $roleName = 'default'; // أو أي قيمة أخرى
            }

            // جلب الصلاحيات
            $user_permissions = $user->permissions() ? $user->permissions()->pluck('back_route_name')->toArray() : [];
            // dd($user_permissions);
            if ($permission && (in_array($permission, $user_permissions) || $roleName == 'admin')) {

                return $next($request);
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Unauthorized',
                    'data' => null
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => trans('You must log in first.'),
                'data' => null
            ], 401);
        }
    }

    // في CustomPermissionMiddleware
    // public function handle($request, Closure $next, $permission = null, $guard = null)
    // {
    //     if (Auth::guard('api')->check()) {
    //         $user = Auth::guard('api')->user();

    //         // جلب المستخدم مع العلاقة role
    //         $user = UsersAccount::with('role')->find($user->id);  // تأكد من تحميل العلاقة role

    //         // جلب الصلاحيات بناءً على الدور
    //         $user_permissions = $user->permissions()->pluck('back_route_name')->toArray();  // تأكد أن permissions هي علاقة وليس دالة
    //         dd($user_permissions);  // تحقق من الصلاحيات

    //         if ($permission && (in_array($permission, $user_permissions) || $user->role->name == 'admin')) {
    //             return $next($request);
    //         } else {
    //             return response()->json([
    //                 'status' => 'fail',
    //                 'message' => 'Unauthorized',
    //                 'data' => null
    //             ], 403);
    //         }
    //     } else {
    //         return response()->json([
    //             'status' => 'fail',
    //             'message' => trans('You must log in first.'),
    //             'data' => null
    //         ], 401);
    //     }
    // }
}
