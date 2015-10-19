<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = [
    	'name'
    ];



    public function user() {
        return $this->belongsToMany('App\User', 'places_users');
    }
}
