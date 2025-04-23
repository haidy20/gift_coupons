<?php
namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
            'phone' => $this->phone,
            'country_name' => $this->country->country_name,
            'image' => $this->media->file_path ?? asset('images/default.png'),
        ];
    }
}