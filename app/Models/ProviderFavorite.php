<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ProviderFavorite extends Model implements JWTSubject
{
    use HasFactory;
    // تحديد اسم الجدول
    protected $table = 'provider_favorites';

    // استخدام guarded بدلاً من fillable
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    // تنفيذ واجهة JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // إرجاع المفتاح الأساسي
    }

    public function getJWTCustomClaims()
    {
        return []; // يمكنك إضافة أي مطالبات مخصصة هنا إذا لزم الأمر
    }
    public function provider()
    {
        return $this->belongsTo(UsersAccount::class, 'provider_id');
    }

    public function user()
    {
        return $this->belongsTo(UsersAccount::class, 'user_id');
    }
}
