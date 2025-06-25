<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;
    
     protected $fillable = [
        'amount',
        'userid',
        'trade_amount',
        'commission',
        'number',
        'games_no',
        'game_id',
        'userid',
        'order_id',
        'created_at',
        'updated_at',
        'status',
    ];
    
    //// subordinate_data ///
    
     public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }
    
    
    public function bets()
{
    return $this->hasMany(Bet::class, 'userid'); // 'user_id' is foreign key in bets table
}

public function payins()
{
    return $this->hasMany(Payin::class, 'user_id'); // 'user_id' is foreign key in payins table
}

public function mlmLevel()
{
    return $this->hasOne(MlmLevel::class, 'user_id'); // or belongsTo, depending on schema
}

    
     //// subordinate_data ///
    
    
    // protected $table = 'bets';

    // public function gameSetting()
    // {
    //     return $this->belongsTo(GameSetting::class, 'game_id');
    // }

    // public function virtualGame()
    // {
    //     // Adjust the relationship as needed. If 'number' is not a field in the 'bets' table, remove it.
    //     return $this->belongsTo(VirtualGame::class, 'game_id');
    // }
}
