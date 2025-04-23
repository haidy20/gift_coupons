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
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 401);
        }

        $feedback = Feedback::create(array_merge(
            [
                'user_id' => $user->id,
                'status' => "pending"
            ],
            $request->validated()
        ));

        // ✅ إرسال إشعار لكل الأدمنز
        $superAdmin = UsersAccount::where('role', 'superAdmin')->first();
        // $superAdmin->notify(new GeneralNotification('Feedback Request', "User {$user->username} submitted a feedback and is waiting for approval."));
        $superAdmin->notify(new GeneralNotification(
            trans('messages.feedback_request_title'),
            trans('messages.feedback_request_body', ['username' => $user->username])
        ));


        return response()->json([
            'status' => 'success',
            'message' => trans('messages.feedback_request_sent'),
            'data' => new UserFeedbackResource($feedback),
        ], 200);
    }

    // ✅ عرض التقييمات الخاصة بمستخدم معين
    public function show()
    {
        $feedbacks = Feedback::where('status', 'approved')->get();

        if ($feedbacks->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.no_feedbacks_found'),
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.all_feedbacks_retrieved'),
            'data' => UserFeedbackResource::collection($feedbacks)
        ], 200);
    }
}
