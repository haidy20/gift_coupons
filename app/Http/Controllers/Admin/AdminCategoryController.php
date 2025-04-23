<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

// Requests
use App\Http\Requests\Admin\AdminCategoryRequest;
// Resources
use App\Http\Resources\Admin\AdminCategoryResource;

class AdminCategoryController extends Controller
{
    // Fetch all categories
    public function index()
    {
        $user = auth('api')->user();
        // $user = auth('api')->user();

        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $categories = Category::get();
        // $categories = Category::all();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.categories_retrieved_successfully'),
            'data' => AdminCategoryResource::collection($categories),
        ], 200);
    }


    public function create(AdminCategoryRequest $request)
    {
        $user = auth('api')->user();
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $category = Category::create($request->validated());
        // التأكد من أن هناك صورة مرفوعة
        if ($request->hasFile('image')) {
            $category->setImageAttribute($request->file('image'));
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.category_created_successfully'),
            'data' => new AdminCategoryResource($category),
        ], 200);
    }


    // Show a single category
    public function show($id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.category_not_found'),
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.category_retrieved_successfully'),
            'data' => new AdminCategoryResource($category),
        ], 200);
    }

    // Update a category
    public function update(AdminCategoryRequest $request, $id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.category_not_found'),
                'data' => null
            ], 404);
        }

        $data = $request->validated();

        $category->update($data);

        if ($request->hasFile('image')) {
            $category->image = $request->file('image'); // Automatically uses the setter
        }
        // return response()->json($category);
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.category_updated_successfully'),
            'data' => new AdminCategoryResource($category),
        ], 200);
    }

    // Delete a category
    public function destroy($id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.category_not_found'),
                'data' => null
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.category_deleted_successfully'),
            'data' => null,
        ], 200);
    }
}
