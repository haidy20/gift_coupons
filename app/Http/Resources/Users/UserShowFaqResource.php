<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserShowFaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $faq = $this['faq'];  // الحصول على كائن FAQ
        $locale = $this['locale']; // الحصول على اللغة المطلوبة
        
        // استخراج الترجمة المطلوبة فقط
        $translation = $faq->translations->where('locale', $locale)->first();

        return [
            'id' => $faq->id,
            'locale' => $locale,
            'question' => $translation ? $translation->question : null,
            'answer' => $translation ? $translation->answer : null,
        ];
    }
}
