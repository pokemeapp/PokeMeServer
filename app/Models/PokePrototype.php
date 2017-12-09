<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PokePrototype extends Model
{
    public function owner()
    {
        return $this->hasOne('App\User', 'id', 'owner_id');
    }

    public function validateOwnership($userId)
    {
        if ($this->owner_id != $userId) {
            throw new \Exception('You are not the owner of that poke prototype.');
        }
    }
}
