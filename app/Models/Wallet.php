<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Wallet extends Model implements JWTSubject
{
    use HasFactory;
    protected $table = 'wallets';

    // Specify guarded fields
    protected $guarded = [
        'id', // Prevent direct assignment to the primary key
        'created_at', // Automatically managed
        'updated_at', // Automatically managed
    ];


    public function provider()
    {
        return $this->belongsTo(UsersAccount::class,'provider_id');
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
}
