<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionTranslation extends Model
{
    use HasFactory;
    protected $table = 'permission_translations';

    // Specify guarded fields
    protected $guarded = [
        'id', // Prevent direct assignment to the primary key
        'created_at', // Automatically managed
        'updated_at', // Automatically managed
    ];


    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
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
