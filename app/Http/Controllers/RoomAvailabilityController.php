<?php

namespace App\Http\Controllers;
use App\Booking;
use App\Room;
use App\Customer;
use App\Service;
use App\BookingService;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Support\Facades\Crypt;
use File;
Use Exception;

class RoomAvailabilityController extends Controller
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
            $bookings = DB::table('bookings')->select('bookings.id','booking_number','book_type','booking_from','booking_to','payment_status','primary_phone','customers.first_name','customers.last_name','rooms.room_name','rooms.room_no')
            ->join('rooms', function($join) {
                $join->on('rooms.id', '=', 'bookings.room_id');
            })
            ->join('customers', function($join) {
                $join->on('customers.booking_id', '=', 'bookings.id');
            });

            if (request()->has('q')) 
            {
                $booking = request('q');
                $bookings = $bookings->where(function($where) use($booking){
                $where->where('book_type','LIKE','%' .$booking.'%')
                    ->orWhere('customers.first_name','LIKE','%' .$booking.'%')
                    ->orWhere('customers.last_name','LIKE','%' .$booking.'%')
                    ->orWhere('payment_status','LIKE','%' .$booking.'%')
                    ->orWhere('primary_phone','LIKE','%' .$booking.'%')
                    ->orWhere('rooms.room_name','LIKE','%' .$booking.'%')
                    ->orWhere('rooms.room_no','LIKE','%' .$booking.'%');
                });
            }

            if ((request()->has('room_name')) || (request()->has('book_type')) || (request()->has('booking_number')) || (request()->has('customer_name')) || (request()->has('payment_status')))
            {
                $room_name = request('room_name');
                $book_type = request('book_type');
                $booking_number = request('booking_number');
                $customer_name = request('customer_name');
                $payment_status = request('payment_status');

                if($room_name){
                    $bookings = $bookings->where('rooms.room_name','=',$room_name);
                }
                if($book_type){
                    $bookings = $bookings->where('book_type','=',$book_type);
                }
                if($booking_number){
                    $bookings = $bookings->where('bookings.id','>=',$booking_number);
                }
                if($customer_name){
                    $bookings = $bookings->where('customers.id','<=',$customer_name);
                }
                if($payment_status){
                    $bookings = $bookings->where('payment_status','=',$payment_status);
                }

            }

            $bookings = $bookings->orderBy('bookings.updated_at', 'desc')->paginate(25);
            $pagination = $bookings->appends(array('q' =>request('q'),'room_name' =>request('room_name'),'book_type' =>request('book_type'),'booking_number' =>request('booking_number'),'customer_name' =>request('customer_name'),'payment_status' =>request('payment_status')));
            $rooms = Room::all();
            $booking_numbers = Booking::all();
            $customers = Customer::all();
            return view('/booking/index',compact('bookings','rooms','booking_numbers','customers'));
            
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
    public function create()
    {
        try 
        {
            return view('room-availability/create');
        } 
        catch (Exception  $e)
        {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        try 
        {
            $booking_from = request('booking_from');
            $booking_to = request('booking_to');

            if(request('booking_to') < request('booking_from')){
                return redirect()->back()->withInput(request()->all())->with('status', 'Booking from date greater than booking to date!');
            }

            $bookings = DB::table('bookings')->select('bookings.id','book_type','booking_from','booking_to','room_id','payment_status','primary_phone','rooms.room_name','rooms.room_no')
            ->join('rooms', function($join) {
                $join->on('rooms.id', '=', 'bookings.room_id');
            })
            ->where('rooms.current_status','=','Active')
            ->whereBetween('booking_from', [$booking_from, $booking_to])
            ->orWhereBetween('booking_to', [$booking_from, $booking_to])
            ->get();

            $room_number = array();
            foreach($bookings as $booking){
                $room_number[] = $booking->room_id;
            }

            $rooms = DB::table('rooms')->select('rooms.id','room_categories.room_category_name','room_name','room_no','room_capacity','room_max','room_rate','current_status')
            ->join('room_categories', function($join) {
                $join->on('room_categories.id', '=', 'rooms.category_id');
            })
            ->where('current_status','=','Active')
            ->whereNotIn('rooms.id',$room_number)
            ->get();

            if($rooms->isEmpty()){
                return redirect()->back()->withInput(request()->all())->with('status', 'There are no rooms available !');
            }
            return view('room-availability/index',compact('rooms','booking_from','booking_to'));
        } 
        catch (Exception  $e)
        {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try 
        {
            $bookings = DB::table('bookings')->select('bookings.id','booking_number','book_type','booking_from','booking_to','booking_fee','number_of_people','reference','book_notes','primary_phone','primary_email','filenames','payment_status','rooms.room_name','rooms.room_no')
            ->join('rooms', function($join) {
                $join->on('rooms.id', '=', 'bookings.room_id');
            })
            ->where('bookings.id','=',Crypt::decrypt($id))
            ->first();

            $booking_services = DB::table('booking_services')->select('booking_services.id','services.service_name','booking_services.service_fee')
            ->join('services', function($join) {
                $join->on('services.id', '=', 'booking_services.service_id');
            })
            ->where('booking_services.booking_id','=',Crypt::decrypt($id))
            ->get();

            $service_fee = 0;
            foreach($booking_services as $booking_service)
            {
                $service_fee = $service_fee + $booking_service->service_fee;
            }
            $bookings->service_fee = $service_fee;
            $customers = Customer::where('booking_id','=',Crypt::decrypt($id))->first();
            return view('booking/show',compact('bookings','customers','booking_services'));
        } 
        catch (Exception  $e) 
        {
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
        try 
        {
            $bookings = DB::table('bookings')->select('bookings.id','booking_number','book_type','booking_from','booking_to','number_of_people','reference','book_notes','primary_phone','primary_email','filenames','payment_status','rooms.room_name','rooms.room_no')
            ->join('rooms', function($join) {
                $join->on('rooms.id', '=', 'bookings.room_id');
            })
            ->where('bookings.id','=',Crypt::decrypt($id))
            ->first();
            $booking_services = DB::table('booking_services')->select('booking_services.id','services.service_name','booking_services.service_fee')
            ->join('services', function($join) {
                $join->on('services.id', '=', 'booking_services.service_id');
            })
            ->where('booking_services.booking_id','=',Crypt::decrypt($id))
            ->get();
            $customers = DB::table('customers')->where('booking_id','=',Crypt::decrypt($id))->first();
            return view('booking/edit',compact('bookings','customers','booking_services'));
        } 
        catch (Exception  $e) 
        {
            return redirect()->back()->with('status',$e->getMessage());
        }
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
        try 
        {
            $customers = Customer::where('booking_id','=',$id)->first();
            if($customers)
            {
                $customers->first_name = $request->first_name;
                $customers->last_name = $request->last_name;
                $customers->nic = $request->nic;
                $customers->phone = $request->phone;
                $customers->email = $request->email;
                $customers->mobile = $request->mobile;
                $customers->address = $request->address;
                $customers->country = $request->country;

                if($request->has('customer_filenames')) 
                {
                    foreach($request->file('customer_filenames') as $file)
                    {
                        $name =$file->getClientOriginalName();
                        $file->move(public_path('documents/customers'), $name);
                        $data[] = $name;
                    }
                    if($request->hidden_customer_filenames)
                    {
                        for($i=0;$i<count(json_decode($request->hidden_customer_filenames));$i++)
                        {
                            $data[] = json_decode($request->hidden_customer_filenames)[$i];
                        }
                    }
                    $customers->customer_filenames = json_encode($data);
                }
                else
                {
                    $image_name=$request->hidden_customer_filenames;
                    $customers->customer_filenames=$image_name;
                }
            }
            $customers->save();

            $bookings = Booking::findOrFail($id);
            if($bookings)
            {
                $bookings->book_type = $request->book_type;
                $bookings->number_of_people = $request->number_of_people;
                $bookings->reference = $request->reference;
                $bookings->book_notes = $request->book_notes;
                $bookings->primary_phone = $request->primary_phone;
                $bookings->primary_email = $request->primary_email;

                if($request->has('filenames')) 
                {
                    foreach($request->file('filenames') as $file)
                    {
                        $name =$file->getClientOriginalName();
                        $file->move(public_path('documents/bookings'), $name);
                        $data2[] = $name;
                    }
                    if($request->hidden_filenames)
                    {
                        for($i=0;$i<count(json_decode($request->hidden_filenames));$i++)
                        {
                            $data2[] = json_decode($request->hidden_filenames)[$i];
                        }
                    }
                    $bookings->filenames = json_encode($data2);
                }
                else
                {
                    $image_name=$request->hidden_filenames;
                    $bookings->filenames=$image_name;
                }

            }
            $result = $bookings->save();
            if ($result)
            {
               return redirect('/bookings')->with('status', 'Booking Details Updated Successfully!');
            } 
            else 
            {
               return redirect()->back()->with('status', 'Something Went Wrong.');
            }
        } 
        catch (Exception  $e) 
        {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try 
        {
            $data = Booking::find(Crypt::decrypt($id));

            for($i=0;$i<count(json_decode($data->filenames));$i++)
            {
                File::delete(public_path('documents/bookings/'.json_decode($data->filenames)[$i]));
            }

            $customer = Customer::where('booking_id','=',Crypt::decrypt($id))->first();

            for($i=0;$i<count(json_decode($customer->customer_filenames));$i++)
            {
                File::delete(public_path('documents/customers/'.json_decode($customer->customer_filenames)[$i]));
            }
            $customer->delete();

            $result = $data->delete();
            if ($result)
            {
               return redirect('/bookings')->with('status', 'Booking Details Deleted Successfully!');
            } 
            else 
            {
               return redirect()->back()->with('status', 'Something Went Wrong.');
            }
        } 
        catch (Exception  $e) 
        {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }

    public function destroyFile($id,$file_id)
    {
        try 
        {
            $booking = Booking::find(Crypt::decrypt($id));
            for($i=0;$i<count(json_decode($booking->filenames));$i++)
            {
                $data[] = json_decode($booking->filenames)[$i];
            }
            unset($data[$file_id]);
            $data2 = array_values($data);

            File::delete(public_path('documents/bookings/'.json_decode($booking->filenames)[$file_id]));

            $booking->filenames = json_encode($data2);
            $result = $booking->save();

            if ($result)
            {
               return redirect()->back()->with('status', 'Booking File Details Deleted Successfully!');
            } 
            else 
            {
               return redirect()->back()->with('status_error', 'Something Went Wrong.');
            }
        } 
        catch (Exception  $e) 
        {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }
}







