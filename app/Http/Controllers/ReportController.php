<?php

namespace App\Http\Controllers;
use App\Payment;
use App\Customer;
use App\Room;
use App\Booking;
use App\BookingService;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Support\Facades\Crypt;
Use Exception;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try 
        {
            $payments = DB::table('payments')->select('payments.id','payment_type','payment_date','payment_amount','rooms.room_name','rooms.room_no','customers.first_name','customers.last_name')
            ->join('bookings', function($join) {
                $join->on('bookings.id', '=', 'payments.booking_id')
                ->join('rooms', function($join) {
                    $join->on('rooms.id', '=', 'bookings.room_id');
                });
            })
            ->join('customers', function($join) {
                $join->on('customers.booking_id', '=', 'payments.booking_id');
            });

            if (request()->has('q')) 
            {
                $payment = request('q');
                $payments = $payments->where(function($where) use($payment){
                $where->where('payment_type','LIKE','%' .$payment.'%')
                    ->orWhere('payment_date','LIKE','%' .$payment.'%')
                    ->orWhere('payments.room_rate','LIKE','%' .$payment.'%')
                    ->orWhere('rooms.room_name','LIKE','%' .$payment.'%')
                    ->orWhere('rooms.room_no','LIKE','%' .$payment.'%')
                    ->orWhere('customers.first_name','LIKE','%' .$payment.'%')
                    ->orWhere('customers.last_name','LIKE','%' .$payment.'%');
                });
            }

            if ((request()->has('customer_name')) || (request()->has('room_name')) || (request()->has('payment_type')) || (request()->has('payment_from')) || (request()->has('payment_to')))
            {

                $customer_name=request('customer_name');
                $room_name=request('room_name');
                $payment_type=request('payment_type');
                $payment_from=request('payment_from');
                $payment_to=request('payment_to');

                if($customer_name){
                    $payments = $payments->where('customers.id','=',$customer_name);
                }
                if($room_name){
                    $payments = $payments->where('rooms.id','=',$room_name);
                }
                if($payment_type){
                    $payments = $payments->where('payments.payment_type','=',$payment_type);
                }
                if($payment_from){
                    $payments = $payments->where('payments.payment_date','>=',$payment_from);
                }
                if($payment_to){
                    $payments = $payments->where('payments.payment_date','<=',$payment_to);
                }

            }

            $payments = $payments->orderBy('payments.updated_at', 'desc')->paginate(25);
            $pagination = $payments->appends(array('q' =>request('q'),'customer_name' =>request('customer_name'),'room_name' =>request('room_name'),'payment_type' =>request('payment_type'),'payment_from' =>request('payment_from'),'payment_to' =>request('payment_to')));
            $customers = Customer::all();
            $rooms = Room::all();
            return view('/report/index',compact('payments','customers','rooms'));
            
        } 
        catch (Exception  $e) 
        {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function print()
    {
        try 
        {
            $payments = DB::table('payments')->select('payments.id','payment_type','payment_date','payment_amount','rooms.room_name','rooms.room_no','customers.first_name','customers.last_name')
            ->join('bookings', function($join) {
                $join->on('bookings.id', '=', 'payments.booking_id')
                ->join('rooms', function($join) {
                    $join->on('rooms.id', '=', 'bookings.room_id');
                });
            })
            ->join('customers', function($join) {
                $join->on('customers.booking_id', '=', 'payments.booking_id');
            });

            if (request()->has('q')) 
            {
                $payment = request('q');
                $payments = $payments->where(function($where) use($payment){
                $where->where('payment_type','LIKE','%' .$payment.'%')
                    ->orWhere('payment_date','LIKE','%' .$payment.'%')
                    ->orWhere('payments.room_rate','LIKE','%' .$payment.'%')
                    ->orWhere('rooms.room_name','LIKE','%' .$payment.'%')
                    ->orWhere('rooms.room_no','LIKE','%' .$payment.'%')
                    ->orWhere('customers.first_name','LIKE','%' .$payment.'%')
                    ->orWhere('customers.last_name','LIKE','%' .$payment.'%');
                });
            }

            if ((request()->has('customer_name')) || (request()->has('room_name')) || (request()->has('payment_type')) || (request()->has('payment_from')) || (request()->has('payment_to')))
            {

                $customer_name=request('customer_name');
                $room_name=request('room_name');
                $payment_type=request('payment_type');
                $payment_from=request('payment_from');
                $payment_to=request('payment_to');

                if($customer_name){
                    $payments = $payments->where('customers.id','=',$customer_name);
                }
                if($room_name){
                    $payments = $payments->where('rooms.id','=',$room_name);
                }
                if($payment_type){
                    $payments = $payments->where('payments.payment_type','=',$payment_type);
                }
                if($payment_from){
                    $payments = $payments->where('payments.payment_date','>=',$payment_from);
                }
                if($payment_to){
                    $payments = $payments->where('payments.payment_date','<=',$payment_to);
                }

            }

            $payments = $payments->orderBy('payments.payment_date', 'asc')->get();
            $customers = Customer::all();
            $rooms = Room::all();
            return view('/report/print',compact('payments','customers','rooms'));
            
        } 
        catch (Exception  $e) 
        {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }
}





