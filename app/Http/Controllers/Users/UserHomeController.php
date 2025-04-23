<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\UsersAccount;
// Requests

use App\Http\Requests\Users\SearchProviderRequest;
// Resources
use App\Http\Resources\Users\UserHomeCategoryResource;
use App\Http\Resources\Users\ProviderResource;
use App\Http\Resources\Users\HomeCategoriesResource;
use App\Http\Resources\Users\ProviderDetailsResourceResource;


class UserHomeController extends Controller
{
    // Home that anyone see
    public function home()
    {
        $categories = Category::with(['providers' => function ($query) {
            $query->select('id', 'category_id', 'username', 'latitude', 'longitude', 'location')
                ->with('vouchers')
                ->with('media');
        }])->get();

        return response()->json([
            'status'=>'success',
            'message' => trans('messages.home_retrieved_successfully'),
            'data' => new UserHomeCategoryResource($categories),
        ], 200);
    }

    // Search Provider in all categories
    public function searchProvider(SearchProviderRequest $request)
    {
        $query = $request->input('query');

        $providers = UsersAccount::where('role', 'provider')
            ->when($query, function ($q) use ($query) {
                $q->where('username', 'LIKE', "%$query%");
            })
            ->groupBy('id') // يضمن عدم التكرار
            ->get();


        if ($providers->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.provider_not_found'),            
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.providers_retrieved_successfully'),            
            'data' => ProviderResource::collection($providers),
        ], 200);
    }

    // Search Provider in specific category
    public function searchProvidersByCategory(SearchProviderRequest $request, $categoryId)
    {
        $query = $request->input('query');

        // Fetch providers only within the specified category
        $providers = UsersAccount::where('role', 'provider')
            ->where('category_id', $categoryId)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('username', 'LIKE', "%$query%");
                });
            })
            ->groupBy('id')
            ->get();

        if ($providers->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No providers found matching your search in this category.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Providers retrieved successfully.',
            'data' => ProviderResource::collection($providers),
        ], 200);
    }

    // Show Providers in specific category
    public function getProvidersByCategory($categoryId)
    {
        // جلب كل البروفايدرز في هذه الفئة
        $providers = UsersAccount::where('role', 'provider')
            ->where('category_id', $categoryId)
            ->get();

        // التحقق إذا لم يكن هناك أي بروفايدرز
        if ($providers->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No providers found in this category.',
                'data' => null
            ], 404);
        }

        // إرجاع البيانات في الريسبونس باستخدام الـ Resource لو متاح
        return response()->json([
            'status' => 'success',
            'message' => 'Providers retrieved successfully.',
            'data' => ProviderResource::collection($providers)
        ], 200);
    }


    // Show all categories 
    public function getAllCategories()
    {
        $categories = Category::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully.',
            'data' => HomeCategoriesResource::collection($categories)
        ], 200);
    }

    // Search category in all categories
    public function searchCategory(SearchProviderRequest $request)
    {
        $query = $request->input('query'); // استلام قيمة البحث من الـ search bar

        $categories = Category::where('category_name', 'LIKE', "%{$query}%")->get();

        // التحقق إذا لم يتم العثور على أي فئة
        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No categories found matching your search.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully.',
            'data' => HomeCategoriesResource::collection($categories)
        ], 200);
    }


    // Details of provider
    public function providerDetails($id)
    {
        $provider = UsersAccount::with(['vouchers' => function ($query) {
            $query->whereRaw('DATE_ADD(start_date, INTERVAL duration_days DAY) >= CURDATE()');
        }])->findOrFail($id);

        if ($provider->vouchers->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.provider_has_no_active_vouchers'),
                'data' => null
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.provider_details_retrieved'),
            'data' => new ProviderDetailsResourceResource($provider)
        ], 200);
    }
}
