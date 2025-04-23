<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Policy extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'policies';

    // استخدام guarded بدلاً من fillable لتأمين البيانات
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];


        // علاقة المصطلح بالترجمات
        public function translations()
        {
            return $this->hasMany(PolicyTranslation::class, 'policy_id');
        }
    
        // إحضار الترجمة بناءً على اللغة المطلوبة
        public function translation($locale)
        {
            return $this->translations()->where('locale', $locale)->first();
        }

        // public function policy()
        // {
        //     return $this->belongsTo(PolicyTranslation::class, 'policy_id'); // تأكد من استخدام "terms_id"
        // }
    
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
}
