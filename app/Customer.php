<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $guarded = ['id'];

    protected $fillable = [
        'first_name','last_name','nic','phone','email','mobile','address','country','customer_filenames','booking_id',
    ];
}
