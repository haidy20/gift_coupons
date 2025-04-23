<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Checkout extends Model implements JWTSubject
{
    use HasFactory;
    protected $table = 'checkouts';
    // استخدام guarded بدلاً من fillable
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    // العلاقة مع الـ UsersAccount
    public function user()
    {
        return $this->belongsTo(UsersAccount::class, 'user_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    // تنفيذ واجهة JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // إرجاع المفتاح الأساسي
    }

    public function getJWTCustomClaims()
    {
        return []; // يمكنك إضافة مطالبات مخصصة هنا إذا لزم الأمر
    }
}
