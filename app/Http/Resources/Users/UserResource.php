<?php
namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'is_active' => (bool)$this->is_active,
            'country' => $this->country->country_code,
            'image' => $this->media->file_path ?? asset('images/default.png'),

        ];
    }
}