<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvUpdateProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'username' => $this->username,
            'image' => $this->media->file_path ?? asset('images/default.png'),
            'email'=>$this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'country_name' => $this->country->country_name,
            'category' => $this->category->category_name,
        ];  
    
    }
}
