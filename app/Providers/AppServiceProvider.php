<?php

namespace App\Providers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        // Using Closure based composers
        view()->composer('home', function ($view) {
            $rooms = \DB::table('rooms')->get();
            $room_total = $rooms->count();
            $view->room_total = $room_total;
        });
        view()->composer('home', function ($view) {
            $categories = \DB::table('room_categories')->get();
            $categories_total = $categories->count();
            $view->categories_total = $categories_total;
        });
        view()->composer('home', function ($view) {
            $customers = \DB::table('customers')->get();
            $customer_total = $customers->count();
            $view->customer_total = $customer_total;
        });
        view()->composer('home', function ($view) {
            $users = \DB::table('users')->get();
            $user_total = $users->count();
            $view->user_total = $user_total;
        });
        view()->composer('home', function ($view) {
            $bookings = \DB::table('bookings')->select('bookings.id','bookings.booking_fee',\DB::raw('SUM(booking_services.service_fee) as service_fee_total'))
            ->join('booking_services', function($join) {
                $join->on('booking_services.booking_id', '=', 'bookings.id');
            })
            ->groupBy('bookings.id','bookings.booking_fee')
            ->get();
            $booking_total = 0;
            foreach($bookings as $booking){
                $booking_total = $booking_total + $booking->booking_fee + $booking->service_fee_total;
            }
            $view->booking_total = $booking_total;
        });

        view()->composer('home', function ($view) {
            $payments = \DB::table('payments')->get();
            $payment_total = 0;
            foreach($payments as $payment){
                $payment_total = $payment_total + $payment->payment_amount;
            }
            $view->payment_total = $payment_total;
        });
    }
}
