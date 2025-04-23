<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;

class Voucher extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'vouchers';

    // Specify guarded fields
    protected $guarded = [
        'id', // Prevent direct assignment to the primary key
        'random_num',
        'created_at', // Automatically managed
        'updated_at', // Automatically managed
    ];

    // علاقة القسيمة بكود QR واحد فقط
    public function qrCode()
    {
        return $this->hasOne(QrCode::class, 'voucher_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($voucher) {
            $voucher->random_num = Str::random(10); // توليد رقم عشوائي فريد
        });
    }

    public function provider()
    {
        return $this->belongsTo(UsersAccount::class, 'provider_id');
    }

    // Many to many between vouchers and users in (user_vouchers)
    public function users()
    {
        return $this->belongsToMany(UsersAccount::class, 'user_vouchers', 'voucher_id', 'user_id')
            ->withPivot('purchase_date', 'expiry_date', 'used_date','status')
            ->withTimestamps();
    }


    // Many to many between users and vouchers in (voucher_favorites) 
    public function favoritedByUsers()
    {
        return $this->belongsToMany(UsersAccount::class, 'voucher_favorites', 'voucher_id', 'user_id')
            ->withTimestamps();
    }



    // Implement JWTSubject methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_voucher', 'voucher_id', 'cart_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'voucher_id');
    }

    public function scannedUsers()
    {
        return $this->hasMany(ScannedUser::class,'voucher_id');
    }
}
