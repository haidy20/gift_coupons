<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserHomeCategoryResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'categories' => $this->pluck('category_name'),
            'home' => UserHomeCollection::collection($this->collection),

        ];
    }
}