<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_name' => $this->category_name,
            // 'image' => $this->file_path, // إرجاع الرابط فقط
            'image' => AdminMediaResource::collection($this->media),
        ];
    }
}
