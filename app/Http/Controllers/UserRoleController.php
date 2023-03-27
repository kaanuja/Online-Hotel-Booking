<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Auth;
use App\User;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userRoles=DB::table('model_has_roles')->select('users.name as uname','roles.name as rname','model_type','roles.id','model_has_roles.model_id')
        ->join('roles', function($join) {
            $join->on('roles.id', '=', 'model_has_roles.role_id');
        })
        ->join('users', function($join) {
            $join->on('users.id', '=', 'model_has_roles.model_id');
        });

        if (request()->has('q')) {
         $bank=request('q');
         $userRoles = $userRoles->where(function($where) use($bank){
            $where->where('roles.name','LIKE','%' .$bank.'%')
                ->orWhere('users.name','LIKE','%' .$bank.'%')
                ->orWhere('model_type','LIKE','%' .$bank.'%');
        });
        }

        $userRoles = $userRoles->paginate(10);
        $pagination = $userRoles->appends(array('q' =>request('q')));
        return view('/user-role/index',compact('userRoles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $ids  = DB::table('model_has_roles')->pluck('model_id');
        $users = DB::table('users')->whereNotIn('id', $ids)->get();
        $roles = Role::all();
        // $users = User::all();
        return view("user-role/create",compact('roles','users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $role_id=$request->role_id;
        $user_id=$request->user_id;

        $users=DB::table('users')->where('id','=',$user_id)->first();

        $model_has_roles = DB::table('model_has_roles')->select('roles.name as rname')
        ->join('roles', function($join) {
            $join->on('roles.id', '=', 'model_has_roles.role_id');
        })
        ->where('model_id','=',$user_id)
        ->first();

        if($model_has_roles){
            return redirect('/user/user-role/create')->with('status',$users->name.' Already Assigned To '.$model_has_roles->rname.' Role!');
        }

        $role=Role::findById($role_id);
        $user = User::find($user_id);

        $result = $user->assignRole($role);
        
        if ($result)
        {
           return redirect('/user/user-role')->with('status', 'User Role Successfully Assigned!');
        } 
        else 
        {
           return redirect('/user/user-role/create');
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
        $users = User::all();
        $roles = Role::findOrFail(Crypt::decrypt($id));
        $userRoles = DB::table('model_has_roles')->where('role_id', '=',$roles->id)->get();
        return view('user-role/show',compact('userRoles','roles','users'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $users = User::all();
        $roles = Role::findOrFail(Crypt::decrypt($id));
        $assignRoles = DB::table('model_has_roles')->where('role_id', '=',$roles->id)->get();
        return view('assignRole/assignRoleUpdate',compact('assignRoles','roles','users'));
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = DB::table('model_has_roles')->where('model_id','=',Crypt::decrypt($id))->delete();
        if($result){
            return redirect('/user/user-role')->with('status', 'User Role Deleted Successfully!');
        }
    }
}
