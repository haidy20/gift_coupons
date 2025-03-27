<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Transaction extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'transactions';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function provider()
    {
        return $this->belongsTo(UsersAccount::class, 'provider_id');
    }

    public function checkout()
    {
        return $this->belongsTo(Checkout::class, 'checkout_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
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
