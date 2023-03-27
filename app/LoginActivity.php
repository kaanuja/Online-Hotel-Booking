<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
     protected $table = 'login_activities';

     protected $guarded = ['id'];

     protected $fillable = [
        'user_id','user_agent','ip_address'
    ];
}
