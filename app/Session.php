<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'oauth_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['client_id', 'owner_type', 'owner_id'];



    public function user() {
        return $this->belongsTo('App\User', 'owner_id');
    }

    public function accessToken() {
        return $this->hasOne('App\AccessToken', 'session_id');
    }
}
