<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LoginActivity;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class LoginActivityController extends Controller
{
    public function index()
    {
        $loginActivities = DB::table('login_activities')->select('users.name','login_activities.created_at','user_agent','ip_address')
        ->join('users', function($join) {
            $join->on('users.id', '=', 'login_activities.user_id');
        });

        if(request()->has('q')) {
         $login_activity=request('q');
         $loginActivities = $loginActivities->where(function($where) use($login_activity){
            $where->where('users.name','LIKE','%' .$login_activity.'%')
                ->orWhere('login_activities.created_at','LIKE','%' .$login_activity.'%')
                ->orWhere('user_agent','LIKE','%' .$login_activity.'%')
                ->orWhere('login_activities.ip_address','LIKE','%' .$login_activity.'%');
        });
        }

        if((request()->has('user_id')) || (request()->has('login_date')) || (request()->has('ip_address'))){

            $user_id=request('user_id');
            $login_date=request('login_date');
            $ip_address=request('ip_address');

            if($user_id){
                $loginActivities = $loginActivities->where('users.id','=',$user_id);
            }
            if($login_date){
                $loginActivities = $loginActivities->whereDate('login_activities.created_at','=',$login_date);
            }
            if($ip_address){
                $loginActivities = $loginActivities->where('login_activities.ip_address','=',$ip_address);
            }
        }

        $loginActivities = $loginActivities->orderBy('login_activities.created_at', 'desc')->paginate(10);
        $pagination = $loginActivities->appends(array('q' =>request('q')));

        $users = DB::table('login_activities')->select('users.name','users.id')
        ->join('users', function($join) {
            $join->on('users.id', '=', 'login_activities.user_id');
        })
        ->groupBy('users.name','users.id')
        ->get();

        $ip_address = DB::table('login_activities')->select('ip_address')
        ->groupBy('ip_address')
        ->get();

        return view('/login-activity/index',compact('loginActivities','users','ip_address'));
    }
}
