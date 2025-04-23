<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Setting extends Model implements JWTSubject
{
    use HasFactory;
    // تحديد اسم الجدول
    protected $table = 'settings';

    // استخدام guarded بدلاً من fillable
    protected $guarded = [
        'id', // منع تعيين المفتاح الرئيسي مباشرة
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    // تنفيذ واجهة JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // إرجاع المفتاح الأساسي
    }

    public function getJWTCustomClaims()
    {
        return []; // يمكنك إضافة أي مطالبات مخصصة هنا إذا لزم الأمر
    }

    public static function getValue(string $key)
    {
        $setting = self::first(); // Assuming there's only one row in the settings table

        if ($setting && isset($setting->$key)) {
            return $setting->$key; // Return the column value if it exists
        }

        return null; // Return null if the key doesn't match any column
    }
}

