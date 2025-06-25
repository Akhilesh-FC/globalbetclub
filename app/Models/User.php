<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'login_token',
        'u_id',
        'mobile'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        
    ];
    
    public function referrals()
{
    return $this->hasMany(User::class, 'referral_user_id');
}

public function getAllSubordinatesCount()
{
    $result = DB::selectOne("
        WITH RECURSIVE subordinates AS (
            SELECT id FROM users WHERE referral_user_id = ?
            UNION ALL
            SELECT u.id FROM users u INNER JOIN subordinates s ON u.referral_user_id = s.id
        )
        SELECT COUNT(*) as total FROM subordinates
    ", [$this->id]);

    return $result->total ?? 0;
}


}
