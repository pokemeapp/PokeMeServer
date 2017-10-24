<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FriendRequest extends Model
{
    public function owner()
    {
        return $this->hasOne('App\User', 'id', 'owner_id');
    }

    public function target()
    {
        return $this->hasOne('App\User', 'id', 'target_id');
    }
}
