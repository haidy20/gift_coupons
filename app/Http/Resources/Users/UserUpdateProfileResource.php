<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserUpdateProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'country_name' => $this->country->country_name,
            // 'image' => optional($this->media->first())->file_path,
            'image' => $this->media->file_path ?? asset('images/default.png'),

        ];
    }
}
