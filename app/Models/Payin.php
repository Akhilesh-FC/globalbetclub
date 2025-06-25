<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payin extends Model
{
    use HasFactory;
    
    public function payins()
{
    return $this->hasMany(Payin::class, 'user_id'); // 'user_id' is foreign key in payins table
}
}

