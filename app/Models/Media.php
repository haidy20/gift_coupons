<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Media extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'media';

    // استخدام guarded بدلاً من fillable لحماية الحقول
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    public function mediable()
    {
        return $this->morphTo();
    }

    public function getFilePathAttribute($value)
    {
        return $value ? url('storage/' . $value) : null ;
    }

    /**
     * تنفيذ واجهة JWTSubject للحصول على معرّف JWT والمطالبات المخصصة
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // إرجاع المفتاح الأساسي
    }

    public function getJWTCustomClaims()
    {
        return []; // يمكنك إضافة مطالبات مخصصة هنا إذا لزم الأمر
    }
}
