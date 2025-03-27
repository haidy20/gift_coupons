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
                'status' => false,
                'message' => 'Only superAdmin can approve feedbacks.',
                'data' => null,
            ], 403);
        }
        $feedback = Feedback::find($feedbackId);
    
        if (!$feedback) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feedback not found',
                'data' => null
            ], 404);
        }
    
        // ✅ تحديث حالة الفيدباك إلى "مقبول"
        $feedback->update(['status' => 'approved']);

        $user = $feedback->user; // جلب المستخدم المرتبط بالفيدباك
        $user->notify(new GeneralNotification('Feedback Approved', 'Your feedback has been approved and saved.'));
    
        return response()->json([
            'status' => 'success',
            'message' => 'Feedback approved and saved successfully.',
            'data' => new AdminFeedbackResource($feedback)
        ], 200);
    }
    

    // ❌ رفض الفيدباك
    public function rejectFeedback($feedbackId)
    {
        $superAdmin = auth('api')->user();
        if ($superAdmin->role !== 'superAdmin') {
            return response()->json([
                'status' => false,
                'message' => 'Only superAdmin can reject feedbacks.',
                'data' => null,
            ], 403);
        }

        $feedback = Feedback::find($feedbackId);
    
        if (!$feedback) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feedback not found',
                'data' => null
            ], 404);
        }
    
        // ✅ تحديث حالة الفيدباك إلى "مرفوض"
        $feedback->update(['status' => 'rejected']);
    
        // ✅ إرسال إشعار للمستخدم بأن الفيدباك تم رفضه
        $user = $feedback->user;
        $user->notify(new GeneralNotification('Feedback Rejected', 'Your feedback has been rejected by the superAdmin.'));

        return response()->json([
            'status' => 'success',
            'message' => 'Feedback request rejected successfully.',
            'data' => new AdminFeedbackResource($feedback)
        ], 200);
    }
}
