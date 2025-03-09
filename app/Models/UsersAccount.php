<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class UsersAccount extends Model implements JWTSubject, AuthenticatableContract
{
    use HasFactory, HasApiTokens, Authenticatable, Notifiable;

    protected $table = 'users_accounts';

    // Specify guarded fields
    protected $guarded = [
        'id', // Prevent direct assignment to the primary key
        'created_at', // Automatically managed
        'updated_at', // Automatically managed
    ];

    // Hide the password field from being included in responses
    protected $hidden = [
        'password',
    ];

    // ✅ علاقة المستخدم بالكارت (One-to-One)
    public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id');
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
    public function getImageAttribute($value)
    {
        return $value ? url('storage/' . $value) : null;
    }
    public function setImageAttribute($file)
    {
        // إذا كان هناك صورة قديمة، قم بحذفها
        if ($this->media()->exists()) {
            $this->media()->delete();
        }

        // إذا كانت الصورة الجديدة موجودة، قم برفعها وحفظ المسار
        if ($file) {
            $filePath = $file->store('profiles', 'public');
            $this->media()->create([
                'file_path' => $filePath,
                'mediable_id' => $this->id,
                'mediable_type' => $this->role,
            ]);

            $this->image = asset($filePath);
        }
    }


    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }


    // Relationships
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'countries_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // One to many between vouchers and providers
    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'provider_id');
    }

    // Many to many between vouchers and users in (user_vouchers)
    public function userVouchers()
    {
        return $this->belongsToMany(Voucher::class, 'user_vouchers', 'voucher_id', 'user_id')
            ->withPivot('purchase_date', 'expiry_date', 'used_date')
            ->withTimestamps();
    }

    public function activeVouchersCount()
    {
        return $this->vouchers()->where('is_active', 1)->count();
    }

    // Many to many between providers and users in (provider_favorites)
    public function favoriteProviders()
    {
        return $this->belongsToMany(UsersAccount::class, 'provider_favorites', 'user_id', 'provider_id')
            ->where('type', 'provider')
            ->withTimestamps();
    }

    // Many to many between providers and users in fav(not used)
    public function favoriteUsers()
    {
        return $this->belongsToMany(UsersAccount::class, 'provider_favorites', 'provider_id', 'user_id')
            ->where('type', 'user')
            ->withTimestamps();
    }

    // Many to many between users and vouchers in (voucher_favorites) 
    public function favoriteVouchers()
    {
        return $this->belongsToMany(Voucher::class, 'voucher_favorites', 'voucher_id', 'user_id')
            ->withTimestamps();
    }


    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    protected $casts = [
        'subscription_expires_at' => 'datetime',
    ];

    public function checkoutOrders()
    {
        return $this->hasMany(Checkout::class, 'user_id');
    }






    // public function favoriteProviders()
    // {
    //     return $this->belongsToMany(UsersAccount::class, 'provider_favorites', 'user_id', 'provider_id')
    //         ->wherePivot('type', 'provider')
    //         ->withTimestamps();
    // }


    // Define the relationship between the provider and their ratings
    // public function userRatings()
    // {
    //     return $this->hasMany(UserRating::class, 'user_id'); // Assuming a hasMany relationship
    // }
    // public function ProviderRatings()
    // {
    //     return $this->hasMany(ProviderRating::class, 'provider_id'); // Assuming a hasMany relationship
    // }

    // public function provider()
    // {
    //     return $this->belongsTo(Wallet::class,'provider_id');
    // }
}
