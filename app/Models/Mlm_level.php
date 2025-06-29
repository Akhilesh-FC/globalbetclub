<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mlm_level extends Model
{
    use HasFactory;
    
    public function mlmLevel()
{
    return $this->hasOne(MlmLevel::class, 'user_id'); // or belongsTo, depending on schema
}
}
