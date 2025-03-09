<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class SubscriptionTranslation extends Model implements JWTSubject
{
    use HasFactory;
    protected $table = 'subscription_translations';

    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];


    public function subscription()
    {
        return $this->belongsTo(Subscription::class,'subscription_id');
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

}
