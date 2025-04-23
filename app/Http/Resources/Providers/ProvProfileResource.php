<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvProfileResource extends JsonResource
{
    public function toArray($request)
    {
        // استرجاع الاشتراك النشط إن وجد
        $subscription = $this->subscription()->where('is_active', 1)->first();
        $locale = $request->header('Accept-Language', app()->getLocale()); // تحديد اللغة من الهيدر أو أخذ الافتراضية

        $subscriptionData = null;
        if ($subscription) {
            // استرجاع ترجمة الاشتراك بناءً على اللغة الحالية
            $translation = $subscription->translations()->where('locale', $locale)->first();

            $subscriptionData = [
                'price' => $translation->price ?? 0,
                'duration' => $translation->duration ?? 'N/A',
            ];
        }

        return [
            'id' => $this->id,
            'username' => $this->username,
            'image' => $this->media->file_path ?? asset('images/default.png'),
            'role' => $this->role,
            'phone' => $this->phone,
            'country_name' => $this->country->country_name,
            'current_subscription' => $subscriptionData ?? 'No subscription yet',
        ];
    }
}
