<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission = null, $guard = null)
    {

        if (auth('api')->check()) {

            $user = auth('api')->user();

            $permission = $request->route()->getName();

            $user_permissions = $user->permissions() != null && ! empty($user->permissions()) ? $user->permissions()->pluck('back_route_name')->toArray() : [];

            if($permission == null || in_array($permission,$user_permissions) || $user->role == 'superAdmin') {
                return $next($request);
            } else {
                return response()->json(['status'=>'fail','message' => 'Unauthorized', 'data' => null], 403);
            }

        } else {
            return response()->json(['status'=>'fail','message' => trans('dashboard.messages.login_firstly'), 'data' => null], 401);
        }




    }
}
