<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\UsersAccount;
use Illuminate\Http\Request;
use App\Notifications\GeneralNotification;
// Resources
use App\Http\Resources\Admin\AdminFeedbackResource;

class AdminFeedbackController extends Controller
{
    // ✅ الموافقة على الفيدباك
    public function approveFeedback($feedbackId)
    {
        $superAdmin = auth('api')->user();
        if ($superAdmin->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }
        $feedback = Feedback::find($feedbackId);

        if (!$feedback) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.feedback_not_found'),
                'data' => null
            ], 404);
        }

        // ✅ تحديث حالة الفيدباك إلى "مقبول"
        $feedback->update(['status' => 'approved']);

        $user = $feedback->user; // جلب المستخدم المرتبط بالفيدباك
        $user->notify(new GeneralNotification(
            // 'Feedback Approved',
            //  'Your feedback has been approved and saved.'

            trans('messages.feedback_approved_title'),
            trans('messages.feedback_approved_body')
        ));

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.feedback_approved_successfully'),
            'data' => new AdminFeedbackResource($feedback)
        ], 200);
    }


    // ❌ رفض الفيدباك
    public function rejectFeedback($feedbackId)
    {
        $superAdmin = auth('api')->user();
        if ($superAdmin->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.feedback_approved_successfully'),
                'data' => null,
            ], 403);
        }

        $feedback = Feedback::find($feedbackId);

        if (!$feedback) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.feedback_not_found'),
                'data' => null
            ], 404);
        }

        // ✅ تحديث حالة الفيدباك إلى "مرفوض"
        $feedback->update(['status' => 'rejected']);

        // ✅ إرسال إشعار للمستخدم بأن الفيدباك تم رفضه
        $user = $feedback->user;
        $user->notify(new GeneralNotification(
            // 'Feedback Rejected',
            //  'Your feedback has been rejected by the superAdmin.'

            trans('messages.feedback_rejected_title'),
            trans('messages.feedback_rejected_body')
        ));

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.feedback_rejected_successfully'),
            'data' => new AdminFeedbackResource($feedback)
        ], 200);
    }
}
