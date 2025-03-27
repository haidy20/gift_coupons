<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;


class UsersAccount extends Model implements JWTSubject, AuthenticatableContract
{
    use HasFactory, HasApiTokens, Authenticatable, Notifiable ;


    protected $table = 'users_accounts';
    // protected $guard_name = 'api';
    // protected $with = ['roles', 'permissions'];

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

    // public function getImageAttribute($value)
    // {
    //     dd($value);
    //     // $media = $this->media()->first(); // Fetch the latest media record
    //     return $value ? url('storage/' . $value) : null;
    // }


    // public function setImageAttribute($file)
    // {
    //     if ($file) {
    //         if ($this->media) { 
    //             $oldImagePath = storage_path("profiles/".$this->media->file_path); 
    //             dd($this->media->file_path , $oldImagePath);


    //             if (file_exists($oldImagePath)) {
    //                 // dd('ahmed');
    //                 unlink($oldImagePath); // حذف الصورة من السيرفر
    //             }

    //             $this->media()->delete(); // حذف سجل الصورة القديمة من قاعدة البيانات
    //         }

    //         $filePath = $file->store('profiles', 'public');

    //         // التحقق مما إذا كان هناك سجل وسائط موجود
    //             $this->media()->create([
    //                 'file_path' => $filePath,
    //                 'mediable_id' => $this->id,
    //                 'mediable_type' => $this->role ?? 'default_role', // تأكد أن role ليست null
    //             ]);


    //         $this->image = asset($filePath);
    //     }
    // }

    public function getImageAttribute($value)
    {
        return $value ? asset("storage/{$value}") : null; // ✅ التأكد من إرجاع المسار الصحيح
    }

    public function setImageAttribute($file)
    {
        if ($file) {
            // **حذف الصورة القديمة إن وجدت**
            if ($this->media) {
                $oldImagePath = public_path("storage/" . $this->media->file_path);
    
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // 🔥 حذف الصورة من السيرفر
                }
    
                $this->media()->delete(); // 🗄️ حذف السجل القديم من قاعدة البيانات
            }
    
            // **رفع الصورة الجديدة**
            $filePath = $file->store('profiles', 'public');
    
            // **إنشاء سجل جديد في جدول media**
            $this->media()->create([
                'file_path' => $filePath,
                'mediable_id' => $this->id,
                'mediable_type' => $this->role ?? 'default_role',
            ]);
        }
    }
    



    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }


    // Relationships
    public function media()
    {
        return $this->morphOne(Media::class, 'mediable');
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
            ->withPivot('purchase_date', 'expiry_date', 'used_date', 'status')
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

    // user may has many voucher so has many qrcode
    public function qrCode()
    {
        return $this->hasMany(QrCode::class, 'user_id');
    }

    // المستخدمين الذين حصلوا على الفاوتشر وتم تسجيلهم في `scanned_users`
    public function scannedVouchers()
    {
        return $this->hasMany(ScannedUser::class);
    }

    // البروفايدر الذي قام بمسح الفاوتشر
    public function scannedByProvider()
    {
        return $this->hasMany(ScannedUser::class, 'provider_id');
    }


    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'user_id');
    }

    // في UsersAccount
    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'provider_id');
    }

    public function provider()
    {
        return $this->belongsTo(Wallet::class, 'provider_id');
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'provider_id');
    }

    public function withdrawal()
    {
        return $this->hasMany(WithdrawalRequest::class, 'provider_id');
    }
}
