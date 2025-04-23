<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
// use Astrotomic\Translatable\Translatable;


class Term extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'terms';

    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    // علاقة المصطلح بالترجمات
    public function translations()
    {
        return $this->hasMany(TermTranslation::class, 'term_id');
    }

    // إحضار الترجمة بناءً على اللغة المطلوبة
    public function translation($locale)
    {
        return $this->translations()->where('locale', $locale)->first();
    }

    // الحصول على المعرف الخاص بـ JWT (عادةً هو المفتاح الأساسي)
    public function getJWTIdentifier()
    {
        return $this->getKey(); // إرجاع المفتاح الأساسي
    }

    // الحصول على المطالبات المخصصة لـ JWT
    public function getJWTCustomClaims()
    {
        return []; // يمكنك إضافة مطالبات مخصصة هنا
    }

    // public function term()
    // {
    //     return $this->belongsTo(TermTranslation::class, 'terms_id'); // تأكد من استخدام "terms_id"
    // }
}
