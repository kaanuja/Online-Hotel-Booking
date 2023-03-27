<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles=DB::table('roles')->select('id','name');

        if (request()->has('search')) {
         $role=request('search');
         $roles = $roles->where(function($where) use($role){
            $where->where('id','LIKE','%' .$role.'%')
                ->orWhere('name','LIKE','%' .$role.'%');
        });
        }

        $roles = $roles->orderBy('roles.id', 'asc')
        ->paginate(25);
        $pagination = $roles->appends(array('search' =>request('search')));
        return view('/role/index',compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("role/create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $role = Role::create(['name' => $request->role_name]);

        if ($role)
        {
           return redirect('/user/role')->with('status', 'Role Details Successfully Created!');
        } 
        else 
        {
           return redirect('/user/role/create');
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
        $roles = Role::findOrFail(Crypt::decrypt($id));
        return view('role/show',compact('roles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $roles = Role::findOrFail(Crypt::decrypt($id));
        return view('role/edit',compact('roles'));
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
        $roles=Role::findOrFail($id );
        if($roles)
        {
          $roles->name=$request->role_name;
        }
         $roles->save();
         return redirect('/user/role')->with('status', 'Role Details Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Role::find(Crypt::decrypt($id));
        $data->delete();
        return redirect('/user/role')->with('status', 'Role Details Deleted Successfully!');
    }
}
