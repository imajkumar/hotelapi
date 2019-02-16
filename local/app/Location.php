<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Exception;
class Location extends Model {


    protected $table = 'location_directory';


    protected $fillable = [
        'type', 'display_name','center_point','country_id','city','country'
    ];
    public $timestamps = false;



}
