<?php

namespace App\Http\Controllers;
use App\Room;
use App\RoomCategory;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Support\Facades\Crypt;
Use Exception;

class RoomController extends Controller
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
            $rooms = DB::table('rooms')->select('rooms.id','room_categories.room_category_name','room_name','room_no','room_capacity','room_max','room_rate','current_status')
            ->join('room_categories', function($join) {
                $join->on('room_categories.id', '=', 'rooms.category_id');
            });

            if (request()->has('q')) 
            {
                $room = request('q');
                $rooms = $rooms->where(function($where) use($room){
                $where->where('room_categories.room_category_name','LIKE','%' .$room.'%')
                    ->orWhere('room_name','LIKE','%' .$room.'%')
                    ->orWhere('room_no','LIKE','%' .$room.'%')
                    ->orWhere('room_capacity','LIKE','%' .$room.'%')
                    ->orWhere('room_max','LIKE','%' .$room.'%')
                    ->orWhere('room_rate','LIKE','%' .$room.'%')
                    ->orWhere('current_status','LIKE','%' .$room.'%');
                });
            }

            if ((request()->has('room_category')) || (request()->has('room_name')) || (request()->has('room_no')) || (request()->has('current_status')))
            {

                $room_category=request('room_category');
                $room_name=request('room_name');
                $room_no=request('room_no');
                $current_status=request('current_status');

                if($room_category){
                    $rooms = $rooms->where('room_categories.room_category_name','=',$room_category);
                }
                if($room_name){
                    $rooms = $rooms->where('room_name','=',$room_name);
                }
                if($room_no){
                    $rooms = $rooms->where('room_no','=',$room_no);
                }
                if($current_status){
                    $rooms = $rooms->where('current_status','=',$current_status);
                }

            }

            $room_categories = RoomCategory::all();
            $rooms = $rooms->orderBy('rooms.updated_at', 'desc')->paginate(25);
            $pagination = $rooms->appends(array('q' =>request('q'),'room_category' =>request('room_category'),'room_name' =>request('room_name'),'room_no' =>request('room_no'),'current_status' =>request('current_status')));
            return view('/room/index',compact('rooms','room_categories'));
            
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
            $room_categories = RoomCategory::all();
            return view("room/create",compact('room_categories'));
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
            $room->category_id = $request->category_id;
            $room->room_name = $request->room_name;
            $room->room_no = $request->room_no;
            $room->room_capacity = $request->room_capacity;
            $room->room_max = $request->room_max;
            $room->room_rate = $request->room_rate;
            $room->current_status = $request->current_status;
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
            $rooms = DB::table('rooms')->select('rooms.id','room_categories.room_category_name','room_name','room_no','room_capacity','room_max','room_rate','current_status')
            ->join('room_categories', function($join) {
                $join->on('room_categories.id', '=', 'rooms.category_id');
            })
            ->where('rooms.id','=',Crypt::decrypt($id))
            ->first();

            $bookings = DB::table('bookings')->select('bookings.id','booking_number','book_type','booking_from','booking_to','payment_status','primary_phone','customers.first_name','customers.last_name','rooms.room_name','rooms.room_no')
            ->join('rooms', function($join) {
                $join->on('rooms.id', '=', 'bookings.room_id');
            })
            ->join('customers', function($join) {
                $join->on('customers.booking_id', '=', 'bookings.id');
            })
            ->where('rooms.id','=',Crypt::decrypt($id))
            ->orderBy('bookings.updated_at', 'desc')->paginate(25);
            return view('room/show',compact('rooms','bookings'));
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
            $rooms = Room::findOrFail(Crypt::decrypt($id));
            $room_categories = RoomCategory::all();
            return view('room/edit',compact('rooms','room_categories'));
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
            $rooms = Room::findOrFail($id );
            if($rooms)
            {
                $rooms->category_id = $request->category_id;
                $rooms->room_name = $request->room_name;
                $rooms->room_no = $request->room_no;
                $rooms->room_capacity = $request->room_capacity;
                $rooms->room_max = $request->room_max;
                $rooms->room_rate = $request->room_rate;
                $rooms->current_status = $request->current_status;
            }
            $result = $rooms->save();
            if ($result)
            {
               return redirect('/rooms')->with('status', 'Room Details Updated Successfully!');
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
            $data = Room::find(Crypt::decrypt($id));
            $result = $data->delete();
            if ($result)
            {
               return redirect('/rooms')->with('status', 'Room Details Deleted Successfully!');
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
