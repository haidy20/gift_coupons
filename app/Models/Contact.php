<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Contact extends Model implements JWTSubject
{
    use HasFactory;

    // استخدام guarded بدلاً من fillable لتأمين البيانات
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    /**
     * الحصول على المعرف الخاص بـ JWT (عادةً هو المفتاح الأساسي).
     */

     public function country()
     {
         return $this->belongsTo(Country::class, 'countries_id');
     }
     
    public function getJWTIdentifier()
    {
        return $this->getKey(); // إرجاع المفتاح الأساسي
    }

    /**
     * الحصول على المطالبات المخصصة لـ JWT (يمكنك إضافة بيانات مخصصة هنا إذا لزم الأمر).
     */
    public function getJWTCustomClaims()
    {
        return []; // يمكنك إضافة مطالبات مخصصة هنا
    }

}
