<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCrudsUsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
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
            'country' => $this->country->country_name,
            'latitude' => $this->latitude,  // ✅ إضافة الإحداثيات
            'longitude' => $this->longitude, // ✅ إضافة الإحداثيات
            'location' => $this->location, // ✅ إضافة العنوان
            'role_id' => $this->role_id, // ✅ إضافة فئة المستخدم
            'category_id' => $this->category_id, // ✅ إضافة فئة المستخدم
        ];
    }
}
