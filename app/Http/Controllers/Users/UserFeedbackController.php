<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\GeneralNotification;
use App\Models\Feedback;
use App\Models\FeedbackTranslation;

// Requests
use App\Http\Requests\Users\UserCreateFeedbackRequest;

// Responses
use App\Http\Resources\Users\UserFeedbackResource;
use App\Models\UsersAccount;

class UserFeedbackController extends Controller
{
    public function create(UserCreateFeedbackRequest $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 401);
        }

        // // ✅ حفظ الفيدباك بالحالة "معلق" (Pending)
        // $feedback= Feedback::create([
        //     'user_id' => $user->id,
        //     'rating' => $request->rating,
        //     'description' => $request->description,
        //     'status' => 'pending' // <-- تحديد أن الفيدباك بانتظار الموافقة
        // ]);

        $feedback = Feedback::create(array_merge(
            ['user_id' => $user->id,
            'status' => "pending"],
            $request->validated()
        ));

        // ✅ إرسال إشعار لكل الأدمنز
        $superAdmin = UsersAccount::where('role', 'superAdmin')->first();
        $superAdmin->notify(new GeneralNotification('Feedback Request', "User {$user->username} submitted a feedback and is waiting for approval."));

        return response()->json([
            'status' => 'success',
            'message' => 'Feedback request sent successfully. Waiting for admin approval.',
            'data' => new UserFeedbackResource($feedback),
        ], 200);
    }

    // ✅ عرض التقييمات الخاصة بمستخدم معين
    public function show()
    {
        $feedbacks = Feedback::where('status', 'approved')->get();

        if ($feedbacks->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No feedbacks found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'All feedbacks retrieved successfully',
            'data' => UserFeedbackResource::collection($feedbacks)
        ], 200);
    }
}
