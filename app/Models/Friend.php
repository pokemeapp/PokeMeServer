<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function friend()
    {
        return $this->hasOne('App\User', 'id', 'friend_id');
    }

    public function deleteFriendship()
    {
        $reverse =  Friend::where([
            'user_id' => $this->friend_id,
            'friend_id' => $this->user_id
        ])->first();

        $reverse->delete();
        $this->delete();
    }
}
