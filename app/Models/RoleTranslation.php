<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleTranslation extends Model
{
    use HasFactory;
    protected $table = 'role_translations';
    public $timestamps = false; // منع استخدام created_at و updated_at

    // Specify guarded fields
    protected $guarded = [
        'id', // Prevent direct assignment to the primary key
        'created_at', // Automatically managed
        'updated_at', // Automatically managed
        
    ];


    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
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
