<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Country extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'countries';

    // استخدام guarded بدلاً من fillable لتأمين البيانات
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];


    /**
     * علاقة مع UsersAccount.
     */
    public function users_account()
    {
        return $this->hasMany(UsersAccount::class, 'countries_id');
    }

    /**
     * علاقة مع Cities.
     */
    public function cities()
    {
        return $this->hasMany(City::class, 'countries_id');
    }

    /**
     * الحصول على المعرف الخاص بـ JWT (عادةً هو المفتاح الأساسي).
     */
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
