<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Poke extends Model
{

    public function proto()
    {
        return $this->hasOne('App\PokePrototype', 'id', 'prototype_id');
    }

    public function owner()
    {
        return $this->hasOne('App\User', 'id', 'owner_id');
    }

    public function target()
    {
        return $this->hasOne('App\User', 'id', 'target_id');
    }
}
