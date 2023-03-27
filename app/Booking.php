<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $guarded = ['id'];

    protected $fillable = [
        'book_type','booking_from','booking_to','number_of_people','reference','book_notes','primary_phone','primary_email','filenames','payment_status','room_id'
    ];
}
