<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Cart extends Model implements JWTSubject
{
    use HasFactory;
    protected $table = 'carts';


    // استخدام guarded بدلاً من fillable
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'cart_voucher', 'cart_id', 'voucher_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function getVoucherQuantity($voucherId)
    {
        return $this->vouchers()->where('voucher_id', $voucherId)->first()?->pivot->quantity ?? 0;
    }



    public function user()
    {
        return $this->belongsTo(UsersAccount::class, 'user_id');
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
