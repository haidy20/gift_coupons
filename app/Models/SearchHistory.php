<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class SearchHistory extends Model implements JWTSubject
{
    use HasFactory;
    protected $table = 'search_histories';

    // استخدام guarded بدلاً من fillable
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];


    public function provider()
    {
        return $this->belongsTo(UsersAccount::class, 'provider_id');
    }

    // تنفيذ واجهة JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // إرجاع المفتاح الأساسي
    }

    public function getJWTCustomClaims()
    {
        return []; // يمكنك إضافة أي مطالبات مخصصة هنا إذا لزم الأمر
    }
}
