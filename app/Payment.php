<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $guarded = ['id'];

    protected $fillable = [
        'payment_type','payment_date','room_rate','booking_id'
    ];
}
