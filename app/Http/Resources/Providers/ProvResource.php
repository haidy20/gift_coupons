<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'image' => $this->media->file_path ?? asset('images/default.png'),
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'is_active' => (bool)$this->is_active,
            'country' => $this->country->country_code,
        ];
    }
}
