<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UsersAccount;
use App\Notifications\GeneralNotification;
use Illuminate\Notifications\DatabaseNotification;

// Requests
use App\Http\Requests\Admin\AdminSendBulkNotificationsRequest;

// Resources
use App\Http\Resources\Admin\AdminNotificationResource;
use App\Http\Resources\Providers\ProvNotificationResource;
use App\Http\Resources\Users\UserNotificationResource;

class AdminNotificationController extends Controller
{

    public function getNotificationsAndMarkAsRead()
    {

        $user = auth('api')->user();
        // جلب جميع الإشعارات الخاصة بالمستخدم الحالي
        $notifications = $user->notifications;
        // إذا كانت هناك إشعارات غير مقروءة، نقوم بتعليمها كمقروءة
        if (!$notifications->isEmpty()) {
            $notifications->markAsRead();
        }

        // التحقق إذا كانت هناك إشعارات أصلاً
        if ($notifications->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.notfications_not_found'),
                'data' => null
            ], 404);
        }
        // اختيار الـ Resource المناسب حسب نوع المستخدم
        $notificationResource = match ($user->role) {
            'superAdmin' => AdminNotificationResource::collection($notifications),
            'provider' => ProvNotificationResource::collection($notifications),
            'client' => UserNotificationResource::collection($notifications),
        };

        // إرجاع جميع الإشعارات باستخدام Resource
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.notifications_retrieved_successfully'),
            // 'data' => NotificationResource::collection($notificationResource)
            'data' => $notificationResource

        ], 200);
    }

    public function sendBulkNotifications(AdminSendBulkNotificationsRequest $request)
    {
        $user = auth('api')->user();

        // السماح فقط للأدمن بإرسال الإشعارات
        if (!$user || $user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data'=>null
            ], 403);
        }

        $validated = $request->validated();

        // جلب المستخدمين بناءً على الـ IDs
        $users = UsersAccount::whereIn('id', $validated['ids'])->get();

        // إرسال الإشعارات الجماعية لكل مستخدم بشكل فردي
        foreach ($users as $user) {
            $user->notify(new GeneralNotification($validated['title'], $validated['message']));
        }
        // استرجاع الإشعارات الجديدة بعد إرسالها
        // $notifications = auth('api')->user()->notifications;

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.notifications_sent_successfully'),
            'data' => null
        ], 200);
    }


    // حذف إشعار معين
    public function destroy($id)
    {
        $user = auth('api')->user();
        $notification = DatabaseNotification::find($id);

        if (!$notification) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.notfication_not_found'),
                'data' => null

            ], 404);
        }

        // التأكد أن الإشعار يخص المستخدم الحالي
        if ($notification->notifiable_id !== $user->id) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $notification->delete();
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.notfication_deleted'),
            'data' => null
        ], 200);
    }


    public function destroyAll()
    {
        $user = auth('api')->user();

        if (!$user || $user->notifications()->count() == 0) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }

        $user->notifications()->where('notifiable_id', $user->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.notfications_deleted'),
            'data' => null,
        ], 200);
    }
}
