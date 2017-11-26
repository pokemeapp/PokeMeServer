<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];



    public function friends()
    {
        return $this->hasMany('App\Friend', 'user_id', 'id');
    }

    public function friendRequests()
    {
        return $this->hasMany('App\FriendRequest', 'target_id', 'id');
    }

    public static function findForPassport($username)
    {
        return User::where('email', $username)->first();
    }
}
