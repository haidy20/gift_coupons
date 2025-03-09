<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMakeUsersResource extends JsonResource
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
            'latitude' => $this->latitude,  // ✅ إضافة الإحداثيات
            'longitude' => $this->longitude, // ✅ إضافة الإحداثيات
            'location' => $this->location, // ✅ إضافة العنوان
            'category_id' => $this->category_id, // ✅ إضافة فئة المستخدم
        ];
    }
}
