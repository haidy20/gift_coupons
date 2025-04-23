<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ScannedUser extends Model implements JWTSubject
{
    use HasFactory;
    protected $table = 'scanned_users';

    // استخدام guarded بدلاً من fillable
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    // العلاقة مع المستخدم (الكلاينت الذي حصل على الفاوتشر)
    public function user()
    {
        return $this->belongsTo(UsersAccount::class, 'user_id');
    }

    // العلاقة مع البروفايدر الذي قام بمسح الكود
    public function provider()
    {
        return $this->belongsTo(UsersAccount::class, 'provider_id');
    }

    // العلاقة مع الفاوتشر
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
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
