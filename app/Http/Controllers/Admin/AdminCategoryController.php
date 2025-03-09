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
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can see all categories.',
                'data' => null
            ], 403);
        }
        $categories = Category::get();
        // $categories = Category::all();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => AdminCategoryResource::collection($categories),
        ], 200);
    }


    public function create(AdminCategoryRequest $request)
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can create categories.',
                'data' => null
            ], 403);
        }
    
        $category = Category::create($request->validated());
        // التأكد من أن هناك صورة مرفوعة
        if ($request->hasFile('image')) {
            $category->setImageAttribute($request->file('image'));
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new AdminCategoryResource($category),
        ], 200);
    }

    
    // Show a single category
    public function show($id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can show category.', 'data' => null], 403);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['status' => 'error', 'message' => 'Category not found', 'data' => null], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => new AdminCategoryResource($category),
        ], 200);
    }

    // Update a category
    public function update(AdminCategoryRequest $request, $id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can update categories.', 'data' => null], 403);
        }

        $category = Category::find($id);
        if (!$category) {
            return response()->json(['status' => 'error', 'message' => 'Category not found', 'data' => null], 404);
        }

        $data = $request->validated();

        $category->update($data);

        if ($request->hasFile('image')) {
            $category->image = $request->file('image'); // Automatically uses the setter
        }
        // return response()->json($category);
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new AdminCategoryResource($category),
        ], 200);
    }

    // Delete a category
    public function destroy($id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can delete categories.', 'data' => null], 403);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['status' => 'error', 'message' => 'Category not found', 'data' => null], 404);
        }

        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
            'data' => null,
        ], 200);
    }
}
