<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('booking_number');
            $table->string('book_type');
            $table->date('booking_from');
            $table->date('booking_to');
            $table->float('booking_fee', 15, 2);
            $table->integer('number_of_people');
            $table->string('reference')->nullable();
            $table->text('book_notes')->nullable();

            $table->string('primary_phone')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('filenames')->nullable();
            $table->string('payment_status');

            $table->integer('room_id')->unsigned();
            $table->foreign('room_id')->references('id')->on('rooms');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
