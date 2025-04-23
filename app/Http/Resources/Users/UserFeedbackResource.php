<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFeedbackResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->user->username ?? 'Unknown',
            // 'image' => $this->user->media->image ?? null, // إذا كان للمستخدم صورة
            'description' => $this->description,
            'rating' => $this->rating,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
