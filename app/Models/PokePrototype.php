<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PokePrototype extends Model
{
    public function owner()
    {
        return $this->hasOne('App\User', 'id', 'owner_id');
    }
}
