<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Role extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'roles';

    // Specify guarded fields
    protected $guarded = [
        'id', // Prevent direct assignment to the primary key
        'created_at', // Automatically managed
        'updated_at', // Automatically managed
    ];

    /**
     * العلاقة مع الـ Permissions (Many-to-Many)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')
            ->withTimestamps();
    }

    
      // // علاقة المصطلح بالترجمات
      public function translations()
      {
          return $this->hasMany(RoleTranslation::class, 'role_id');
      }
  
      // إحضار الترجمة بناءً على اللغة المطلوبة
      public function translation($locale)
      {
          return $this->translations()->where('locale', $locale)->first();
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
