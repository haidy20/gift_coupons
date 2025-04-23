<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // ✅ جلب الترجمة بناءً على اللغة المختارة
        $locale = $request->header('Accept-Language'); // اللغة الافتراضية الإنجليزية
        $translation = $this->translations()->where('locale', $locale)->first();
        // dd($translation ? $translation->title : 'N/A');

        return [
            'id' => $this->id,
            'title' => $translation ? $translation->title : 'N/A',
            'description' => $translation ? $translation->description : 'N/A',
            'price' => $translation ? $translation->price : 'N/A',
            'duration' => $translation ? $translation->duration : 'N/A',
        ];
    }
}
