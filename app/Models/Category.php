<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Category extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'categories';
    // استخدام guarded بدلاً من fillable
    protected $guarded = [
        'id',
        'created_at', // يتم إدارته تلقائيًا
        'updated_at', // يتم إدارته تلقائيًا
    ];

    // تعديل getter لعرض الصورة بشكل صحيح
    public function getImageAttribute($value)
    {
        return $value ? url('storage/' . $value) : null;
    }

    // Relationships
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function setImageAttribute($file)
    {

        // If there's an existing image, delete it
        if ($this->media()->exists()) {
            $this->media()->delete();
        }

        // If a new image is provided, upload and save its path
        if ($file) {
            $imagePath = $file->store('categories', 'public');
            $this->media()->create([
                'file_path' => $imagePath,
                'mediable_type' => Category::class,  // Use the correct model for the mediable_type
                'mediable_id' => $this->id,
            ]);
            // $this->image = asset($imagePath);

        }
    }


    public function providers()
    {
        return $this->hasMany(UsersAccount::class, 'category_id');
    }

    // تنفيذ واجهة JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // إرجاع المفتاح الأساسي
    }

    public function getJWTCustomClaims()
    {
        return []; // يمكنك إضافة مطالبات مخصصة هنا إذا لزم الأمر
    }
}
