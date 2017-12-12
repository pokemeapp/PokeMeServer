<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Habit extends Model
{
    public function owner()
    {
        return $this->belongsTo('App\User', 'owner_id', 'id');
    }

    public function reject()
    {
        $rejectionCounter = $this->rejected;
        $this->rejected = $rejectionCounter + 1;
        $this->save();
        if ($this->rejected >= 3) {
            return true;
        }
        return false;
    }

    public function done()
    {
        $this->rejected = 0;
        $this->updated_at = Carbon::now();
        $this->save();
    }
}
