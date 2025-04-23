<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;

// Resources
use App\Http\Resources\Admin\AdminShowContactResource;


class AdminContactController extends Controller
{
    // Admin only can see it  
    public function show()
    {
        $user = auth('api')->user();
        if (!$user || $user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        $contacts = Contact::all(); // جلب جميع السجلات
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.contacts_retrieved_successfully'),
            'data' => AdminShowContactResource::collection($contacts), // تحويل البيانات باستخدام الريسورس
        ], 200);
    }

    public function markAsRead($id)
    {
        $user = auth('api')->user();
        if (!$user || $user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.contact_not_found'),
                'data' => null
            ], 404);
        }

        // تحديث read_at إلى الوقت الحالي
        $contact->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.contact_marked_read'),
            'data' => new AdminShowContactResource($contact)
        ], 200);
    }
}
