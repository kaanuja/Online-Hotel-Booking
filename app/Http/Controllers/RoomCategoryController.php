<?php

namespace App\Http\Controllers;
use App\RoomCategory;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Support\Facades\Crypt;
Use Exception;

class RoomCategoryController extends Controller
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
            $room_categories = DB::table('room_categories')->select('id','room_category_name','room_category_description');

            if (request()->has('q')) 
            {
                $room_category = request('q');
                $room_categories = $room_categories->where(function($where) use($room_category){
                $where->where('room_category_name','LIKE','%' .$room_category.'%')
                    ->orWhere('room_category_description','LIKE','%' .$room_category.'%');
                });
            }

            $room_categories = $room_categories->orderBy('room_categories.updated_at', 'desc')->paginate(25);
            $pagination = $room_categories->appends(array('q' =>request('q')));
            return view('/room-category/index',compact('room_categories'));
            
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
            return view("room-category/create");
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
            $room_category = new RoomCategory();
            $room_category->room_category_name = $request->room_category_name;
            $room_category->room_category_description = $request->room_category_description;
            $result = $room_category->save();

            if ($result)
            {
               return redirect('/room-categories')->with('status', 'Room Category Details  Successfully Created!');
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
            $room_categories = RoomCategory::findOrFail(Crypt::decrypt($id));
            return view('room-category/show',compact('room_categories'));
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
            $room_categories = RoomCategory::findOrFail(Crypt::decrypt($id));
            return view('room-category/edit',compact('room_categories'));
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
            $room_categories = RoomCategory::findOrFail($id );
            if($room_categories)
            {
                $room_categories->room_category_name = $request->room_category_name;
                $room_categories->room_category_description = $request->room_category_description;
            }
            $result = $room_categories->save();
            if ($result)
            {
               return redirect('/room-categories')->with('status', 'Room Category Details Updated Successfully!');
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
            $data = RoomCategory::find(Crypt::decrypt($id));
            $result = $data->delete();
            if ($result)
            {
               return redirect('/room-categories')->with('status', 'Room Category Details Deleted Successfully!');
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

