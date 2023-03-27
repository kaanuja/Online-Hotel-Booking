<?php

namespace App\Http\Controllers;
use App\Customer;
use Auth;
use App\BookingService;
use App\Booking;
use App\Service;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
Use Exception;

class BookingServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        try {
            $bookings = DB::table('bookings')->select('bookings.id','booking_number','book_type','booking_from','booking_to','booking_fee','number_of_people','reference','book_notes','primary_phone','primary_email','filenames','payment_status','rooms.room_name','rooms.room_no','rooms.room_rate')
            ->join('rooms', function($join) {
                $join->on('rooms.id', '=', 'bookings.room_id');
            })
            ->where('bookings.id','=',Crypt::decrypt($id))
            ->first();
            $customers = Customer::where('booking_id','=',Crypt::decrypt($id))->first();
            $services = Service::all();
            return view("booking-service/create",compact('bookings','customers','services'));
        } catch (Exception  $e) {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 
        try {

            $service = Service::findOrFail($request->service_id);
            $booking_service = new BookingService();
            $booking_service->service_id = $service->id;
            $booking_service->quantity = $request->quantity;
            $booking_service->service_fee = $service->service_amount*$request->quantity;
            $booking_service->booking_id = $request->booking_id;
            $result = $booking_service->save();

            if ($result)
            {
               return Redirect::to($request->prev_url)->with('status', 'Booking Service Details Successfully Created!');
            } 
            else 
            {
               return Redirect::to($request->prev_url)->with('status', 'Something Went Wrong.');
            }
        } catch (Exception  $e) {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Bus  $bus
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $booking_services = DB::table('booking_services')->select('booking_services.id','services.service_name','booking_services.service_fee','booking_services.quantity')
            ->join('services', function($join) {
                $join->on('services.id', '=', 'booking_services.service_id');
            })
            ->where('booking_services.id','=',Crypt::decrypt($id))
            ->first();
        try {
            return view('booking-service/show',compact('booking_services'));
        } catch (Exception  $e) {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = BookingService::find(Crypt::decrypt($id));
        try {
            $data->delete();
            return redirect()->back()->with('status', 'Booking Service Details Deleted Successfully!');
        } catch (Exception  $e) {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }
}
