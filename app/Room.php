<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';

    protected $guarded = ['id'];

    protected $fillable = [
        'category_id','room_name','room_no','room_capacity','room_max','room_rate',
    ];
}
