<?php

namespace App\Http\Controllers;
use App\Room;
use App\Booking;
use App\Payment;
use App\Customer;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Support\Facades\Crypt;
Use Exception;

class RoomBookingController extends Controller
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
            $customers = DB::table('customers')->select('id','first_name','last_name','nic','phone','email','mobile','country');

            if (request()->has('q')) 
            {
                $customer = request('q');
                $customers = $customers->where(function($where) use($customer){
                $where->where('first_name','LIKE','%' .$customer.'%')
                    ->orWhere('last_name','LIKE','%' .$customer.'%')
                    ->orWhere('nic','LIKE','%' .$customer.'%')
                    ->orWhere('phone','LIKE','%' .$customer.'%')
                    ->orWhere('email','LIKE','%' .$customer.'%')
                    ->orWhere('mobile','LIKE','%' .$customer.'%')
                    ->orWhere('country','LIKE','%' .$customer.'%');
                });
            }

            if ((request()->has('customer_nic')) || (request()->has('customer_mobile')) || (request()->has('customer_email')) || (request()->has('customer_country')) || (request()->has('customer_name')))
            {
                $customer_nic=request('customer_nic');
                $customer_mobile=request('customer_mobile');
                $customer_email=request('customer_email');
                $customer_country=request('customer_country');
                $customer_name=request('customer_name');

                if($customer_nic){
                    $customers = $customers->where('nic','=',$customer_nic);
                }
                if($customer_mobile){
                    $customers = $customers->where('mobile','=',$customer_mobile);
                }
                if($customer_email){
                    $customers = $customers->where('email','=',$customer_email);
                }
                if($customer_country){
                    $customers = $customers->where('country','=',$customer_country);
                }
                if($customer_name){
                    $customers = $customers->where('first_name','=',$customer_name);
                }

            }

            $customers = $customers->orderBy('customers.updated_at', 'desc')->paginate(25);
            $pagination = $customers->appends(array('q' =>request('q'),'customer_nic' =>request('customer_nic'),'customer_mobile' =>request('customer_mobile'),'customer_email' =>request('customer_email'),'customer_country' =>request('customer_country'),'customer_name' =>request('customer_name')));
            return view('/customer/index',compact('customers'));
            
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
            $rooms = Room::all();
            return view("room-booking/create",compact('rooms'));
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
    public function store(Request $request)
    {
        try 
        {
            $room = new Room();
            $room->room_category = $request->room_category;
            $room->room_name = $request->room_name;
            $room->room_no = $request->room_no;
            $room->room_capacity = $request->room_capacity;
            $room->room_max = $request->room_max;
            $room->room_rate = $request->room_rate;
            $room->current_status = $request->current_status;
            $room->save();
            $result = $room->save();

            if ($result)
            {
               return redirect('/rooms')->with('status', 'Room Details  Successfully Created!');
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try 
        {
            $customers = Customer::findOrFail(Crypt::decrypt($id));
            return view('customer/show',compact('customers'));
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
            $customers = Customer::findOrFail(Crypt::decrypt($id));
            return view('customer/edit',compact('customers'));
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
            $customers = Customer::findOrFail($id );
            if($rooms)
            {
                $customers->first_name = $request->first_name;
                $customers->last_name = $request->last_name;
                $customers->nic = $request->nic;
                $customers->phone = $request->phone;
                $customers->email = $request->email;
                $customers->mobile = $request->mobile;
                $customers->address = $request->address;
                $customers->country = $request->country;
                $customers->customer_filenames = $request->customer_filenames;
            }
            $result = $customers->save();
            if ($result)
            {
               return redirect('/customers')->with('status', 'Customer Details Updated Successfully!');
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
            $data = Customer::find(Crypt::decrypt($id));
            $result = $data->delete();
            if ($result)
            {
               return redirect('/customers')->with('status', 'Customer Details Deleted Successfully!');
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
}



