<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
// Requests
use App\Http\Requests\Users\ToggleFavoriteProviderRequest;
// Resources
use App\Http\Resources\Users\ProviderResource;
use App\Models\UsersAccount;
use Illuminate\Http\Request;

class UserProvFavouriteController extends Controller
{
    // Toggle favorite provider
    public function toggleFavoriteProvider(ToggleFavoriteProviderRequest $request)
    {
        $user = auth()->user();
        $providerId = $request->validated()['provider_id'];

        $isFavorite = $user->favoriteProviders()->where('provider_id', $providerId)->exists();

        if ($isFavorite) {
            $user->favoriteProviders()->detach($providerId);
            return response()->json([
                'status' => 'success',
                'message' => 'Provider removed from favorites',
                'data' => null
            ]);
        }

        $user->favoriteProviders()->attach($providerId, ['type' => 'provider']);
        return response()->json([
            'status' => 'success',
            'message' => 'Provider added to favorites',
            'data' => null
        ]);
    }
    // Get all fav providers
    public function getFavoriteProviders()
    {
        $user = auth()->user();
        $favoriteProviders = $user->favoriteProviders;

        if ($favoriteProviders->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No favorite providers found',
                'data' => []
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Favorite providers retrieved successfully',
            'data' => ProviderResource::collection($favoriteProviders),
        ]);
    }
}
