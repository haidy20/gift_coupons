<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvSubUpgradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // ✅ جلب الترجمة بناءً على اللغة المختارة
        $locale = $request->header('Accept-Language', 'en'); // اللغة الافتراضية الإنجليزية
        $translation = $this->translations()->where('locale', $locale)->first();

        return [
            'id' => $this->id,
            // 'title' => $translation ? $translation->title : 'N/A',
            'price' => $translation ? $translation->price : 'N/A',
            'duration' => $translation ? $translation->duration : 'N/A',
        ];
    }
}
