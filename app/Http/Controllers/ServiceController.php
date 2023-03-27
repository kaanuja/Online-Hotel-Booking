<?php

namespace App\Http\Controllers;
use App\Service;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Support\Facades\Crypt;
use File;
Use Exception;

class ServiceController extends Controller
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
            $services = DB::table('services')->select('services.id','service_name','service_amount');

            if (request()->has('q')) 
            {
                $service = request('q');
                $services = $services->where(function($where) use($service){
                $where->where('service_name','LIKE','%' .$service.'%')
                    ->orWhere('service_amount','LIKE','%' .$service.'%');
                });
            }

            $services = $services->orderBy('services.updated_at', 'desc')->paginate(25);
            $pagination = $services->appends(array('q' =>request('q')));
            return view('/service/index',compact('services'));
            
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
            return view('service/create');
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
            $service = new Service();
            $service->service_name = $request->service_name;
            $service->service_amount = $request->service_amount;

            $result = $service->save();

            if ($result)
            {
               return redirect('/services')->with('status', 'Service Details  Successfully Created!');
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
            $services = Service::findOrFail(Crypt::decrypt($id));
            return view('service/show',compact('services'));
        } 
        catch (Exception  $e) 
        {
            return redirect()->back()->with('status',$e->getMessage());
        }
    }

    public function getServiceData($id)
    {
        $service = DB::table('services')->where('id','=',$id)
        ->first();
        return response()->json($service, 200);
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
            $services = Service::findOrFail(Crypt::decrypt($id));
            return view('service/edit',compact('services'));
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
            $services = Service::findOrFail($id);
            if($services)
            {
                $services->service_name = $request->service_name;
                $services->service_amount = $request->service_amount;
            }

            $result = $services->save();
            if ($result)
            {
               return redirect('/services')->with('status', 'Service Details Updated Successfully!');
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
            $data = Service::find(Crypt::decrypt($id));
            $result = $data->delete();

            if ($result)
            {
               return redirect('/services')->with('status', 'Service Details Deleted Successfully!');
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







