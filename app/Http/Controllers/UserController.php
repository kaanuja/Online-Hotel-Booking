<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Auth;
use App\User;
use App\Staff;
use App\Dealer;
use App\SubDealer;
use App\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if ( auth()->user()->hasRole('Super_Admin') || auth()->user()->hasRole('Admin') ){
             $users=DB::table('users')->select('name','email','password','id');
        }else{
             $users=DB::table('users')->select('name','email','password','id')->where('id','=',auth()->user()->id);
        } 

        if (request()->has('search')) {
         $user=request('search');
         $users = $users->where(function($where) use($user){
            $where->where('name','LIKE','%' .$user.'%')
                ->orWhere('email','LIKE','%' .$user.'%');
                
        });
        }

        $users = $users->orderBy('updated_at', 'desc')->paginate(10);
        $pagination = $users->appends(array('search' =>request('search')));
        return view('/user/index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("user/create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect('user/create')->withErrors($validator)->withInput();
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        
        $result = $user->save();

        if ($result)
        {
           return redirect('/user')->with('status', 'User Details  Successfully Added!');
        } 
        else 
        {
           return redirect()->back()->with('status', 'Something Went Wrong.');
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
        $users = User::findOrFail(Crypt::decrypt($id));
        return view('user/show',compact('users'));
    }

    public function getUserData($id)
    {
        $users = DB::table('users')->where('user_type','=',$id)
        ->get();
        return response()->json($users, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $users = User::findOrFail(Crypt::decrypt($id));
        return view('user/edit',compact('users'));
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
        $users=User::findOrFail($id);

        if($users)
        {
          if($users->password == $request->password){
            $users->password=$request->password;
          }
          else{
            $users->password=Hash::make($request->password);
          }
        }

        $result = $users->save();
        if($result)
        {
           return redirect('/user')->with('status', 'User Details Updated Successfully!');
        } 
        else 
        {
           return redirect()->back()->with('status', 'Something Went Wrong.');
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
        $data = User::find(Crypt::decrypt($id));
        $result = $data->delete();
        if($result)
        {
           return redirect('/user')->with('status', 'User Details Deleted Successfully!');
        } 
        else 
        {
           return redirect()->back()->with('status', 'Something Went Wrong.');
        }
    }
}
