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

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rolePermissions=DB::table('role_has_permissions')->select('role_id')->groupBy('role_id')->paginate(25);
        $roles = Role::all();
        return view('/role-permission/index',compact('rolePermissions','roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles=Role::all();
        $permissions=Permission::all();
        return view("role-permission/create",compact('roles','permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $inputs = $request->input();
        $role_name=$request->role_name;

        $role_has_permissions = DB::table('role_has_permissions')
        ->join('roles', function($join) {
            $join->on('roles.id', '=', 'role_has_permissions.role_id');
        })
        ->where('roles.name','=',$role_name)
        ->first();

        if($role_has_permissions){
            return redirect('/user/role-permission/create')->with('status',$role_name.'  Permission Already Created!');
        }
        
        $permission_id = $inputs["permission_id"];
        $permission_name = $inputs["permission"];

        $collection = collect($permission_id);
        

        for($x=0;$x<$collection->count();$x++){

        $role_name=$request->role_name;
        $roles=DB::table('roles')->where('name','like',$role_name)->first();

            $collectionPermission = collect($permission_name);
                if($collectionPermission->contains($collection[$x])){

                    $role=Role::findById($roles->id);
                    $permission=Permission::findById($x+1);
                    $result = $role->givePermissionTo($permission);
                    
                }
        }

        if ($result)
        {
           return redirect('/user/role-permission')->with('status', 'Role Permissions Successfully Created!');
        } 
        else 
        {
           return redirect('/user/role-permission/create');
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
        $permissions=Permission::all();
        $roles = Role::findOrFail(Crypt::decrypt($id));
        $rolePermissions = DB::table('role_has_permissions')->where('role_id', '=',$roles->id)->get();
        return view('role-permission/show',compact('rolePermissions','roles','permissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $permissions=Permission::all();
        $roles = Role::findOrFail(Crypt::decrypt($id));
        $rolePermissions = DB::table('role_has_permissions')->where('role_id', '=',$roles->id)->get();
        return view('role-permission/edit',compact('permissions','roles','rolePermissions'));
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
        $roles = Role::findOrFail(Crypt::decrypt($id));
        $data = DB::table('role_has_permissions')->where('role_id',$roles->id);
        $data->delete();
        return redirect('/user/role-permission')->with('status', 'Role Permissions Deleted Successfully!');
    }
}
