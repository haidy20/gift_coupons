<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMakeUsersResource extends JsonResource
{

    private $token;
    private $expiresIn;

    public function __construct($resource, $token, $expiresIn)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->expiresIn = $expiresIn;
    }

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
            'category_id' => $this->category_id, // ✅ إضافة فئة المستخدم
            'role_id' => $this->role_id, // ✅ إضافة فئة المستخدم
            'access_token' => $this->token,
            'expires_in' => $this->expiresIn,
        ];
    }
}
