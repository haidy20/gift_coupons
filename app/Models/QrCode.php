<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class QrCode extends Model implements JWTSubject
{
    use HasFactory;
    protected $table = 'qr_codes';

    // استخدام guarded بدلاً من fillable
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    // public function getQrCodeUrlAttribute()
    // {
    //     return $this->qr_code_path ? asset('storage/' . $this->qr_code_path) : null;
    // }

    // تنفيذ واجهة JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // إرجاع المفتاح الأساسي
    }

    public function getJWTCustomClaims()
    {
        return []; // يمكنك إضافة أي مطالبات مخصصة هنا إذا لزم الأمر
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function user()
    {
        return $this->belongsTo(UsersAccount::class, 'user_id');
    }
    public function provider()
    {
        return $this->belongsTo(UsersAccount::class, 'provider_id');
    }
    
    public function getFilePathAttribute($value)
    {
        return $value ? url('storage/' . $value) : null ;
    }
}
