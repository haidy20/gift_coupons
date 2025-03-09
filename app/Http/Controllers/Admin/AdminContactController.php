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
        if (!$user || $user->role !== 'admin') {
            return response()->json(['status' => 'error','message' => 'Only admins can see the information.','data'=>null], 403);
        }
        $contacts = Contact::all(); // جلب جميع السجلات
        return response()->json([
            'success' => true,
            'message' => 'Contacts retrieved successfully',
            'data' => AdminShowContactResource::collection($contacts), // تحويل البيانات باستخدام الريسورس
        ], 200);
    }
}
