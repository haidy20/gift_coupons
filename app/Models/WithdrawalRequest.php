<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class WithdrawalRequest extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'withdrawal_requests';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * العلاقة بين الطلب والمحفظة
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'provider_id');
    }

   public function provider()
    {
        return $this->belongsTo(UsersAccount::class, 'provider_id');
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
