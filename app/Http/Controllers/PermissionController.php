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

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions=DB::table('permissions')->select('id','name');

        if (request()->has('search')) {
         $permission=request('search');
         $permissions = $permissions->where(function($where) use($permission){
            $where->where('id','LIKE','%' .$permission.'%')
                ->orWhere('name','LIKE','%' .$permission.'%');
        });
        }

        $permissions = $permissions->orderBy('permissions.created_at', 'desc')->paginate(25);
        $pagination = $permissions->appends(array('search' =>request('search')));
        return view('/permission/index',compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("permission/create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $permission = Permission::create(['name' => $request->permission_name]);

        if ($permission)
        {
           return redirect('/user/permission')->with('status', 'Permission Details Successfully Created!');
        } 
        else 
        {
           return redirect('/user/permission/create');
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
        $permissions = Permission::findOrFail(Crypt::decrypt($id));
        return view('permission/show',compact('permissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $permissions = Permission::findOrFail(Crypt::decrypt($id));
        return view('permission/edit',compact('permissions'));
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
        $permissions=Permission::findOrFail($id);
        if($permissions)
        {
          $permissions->name=$request->permission_name;
        }
         $permissions->save();
         return redirect('/user/permission')->with('status', 'Permission Details Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Permission::find(Crypt::decrypt($id));
        $data->delete();
        return redirect('/user/permission')->with('status', 'Permission Details Deleted Successfully!');
    }
}
