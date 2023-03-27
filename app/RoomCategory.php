<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomCategory extends Model
{
    protected $table = 'room_categories';

    protected $guarded = ['id'];

    protected $fillable = [
        'room_category_name','room_category_description'
    ];
}
