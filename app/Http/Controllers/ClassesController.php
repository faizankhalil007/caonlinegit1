<?php

namespace App\Http\Controllers;

use App\Classes;
use Illuminate\Http\Request;
use App\Traits\CommonTraits;
use App\User;
class ClassesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $class_model;
    protected $user_model;
    use CommonTraits;
    public function __construct()
    {
        $this->middleware('auth');

        $this->class_model = new Classes();
        $this->user_model  = new User();
    }

    public function index()
    {
        //
        $all_classes = $this->class_model->get();
        return view('admin/classes_list',['classes_data'=>$all_classes]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('admin/add_edit_class',['form_type'=>1]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name'  => 'required|max:55',
        ]);
        $data = array(
            'name'  => $request->name,
            'created_by'    => auth()->user()->id,
            'created_at'    => date('d-m-Y'),
        );
        $save_class = $this->class_model->insert($data);
        if ($save_class){
            $request->session()->put('success','Operation performed successfully');
            return redirect('classes');
        } else{
            $request->session()->put('error','something went wrong, please try again');
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Classes  $classes
     * @return \Illuminate\Http\Response
     */
    public function show(Classes $classes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Classes  $classes
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
        $class_information  = $this->class_model->where(array('id'=>$request->id))->first();
        return view('admin/add_edit_class',['form_type'=>2,'class_data'=>$class_information]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Classes  $classes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Classes $classes)
    {
        //
        $request->validate([
            'name'  => 'required|max:100',
            'id'    =>  'required'
        ]);
        $update_data = array(
            'name'  =>  $request->name,
            'updated_by'    =>  auth()->user()->id,
            'updated_at'    =>  date('d-m-Y H:i:s a'),
        );
        $update_class = $this->class_model->where(array('id'=>$request->id))->update($update_data);
        if ($update_class){
            $request->session()->put('success','Operation performed successfully');
            return redirect('classes');
        } else{
            $request->session()->put('error','something went wrong, please try again');
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Classes  $classes
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $delete_class = $this->class_model->where(array('id'=>$request->id))->delete();
        if ($delete_class){
            $request->session()->put('success','deleted successfully');
            return back();
        } else{
            $request->session()->put('error','please try again');
            return back();
        }
    }

    public function crate_data_log($id,$data,$operation)
    {
        if ($id){
            $old_data = $this->class_model->where(array('id'=>$id))->first();
            $data = array(
                'old_data'  =>  $old_data,
                'new_data'  =>  $data,
            );
        } else{
            $data = $data;
        }
        $log = $this->crate_log($data,'Classes Controller',$operation);
        if ($log){
            return true;
        } else{
            return false;
        }
    }
    public function send_mobile_code(Request $request){
        $user_id    = auth()->user()->id;
        $user_phone = auth()->user()->phone_number;
        $rand       = rand(1000,9999);
        $data = array(
            'title'   => $user_phone,
            'body'   => 'Your verification code for CA ONLINE TEST '.$rand,
        );
        $send_sms = $this->CustomerPushNotification($data);
//        print_r($send_sms); exit;
        if ($send_sms){
            $update_data = array(
                'verification_code' => $rand
            );
            $update = $this->user_model->where(array('id'=>$user_id))->update($update_data);
            if ($update){
                $request->session()->put('success','check your mobile phone for verification code');
                return back();
            } else{
                $request->session()->put('error','something went wrong please try again');
                return back();
            }
        }
    }
    /* verify code */
    public function verifymobilecode(Request $request){
        $user_id = auth()->user()->id;
        $code    = $request->verification_code;
        if ($code == auth()->user()->verification_code){
            $data = array(
                'email_verified_at' => now(),
                'updated_at'        => now(),
            );
            $verify_code = $this->user_model->where(array('id'=>$user_id))->update($data);
            if ($verify_code){
                $request->session()->put('success','congratulations');
                return redirect('home');
            } else{
                $request->session()->put('error','something went wrong please try again');
                return back();
            }
        } else{
            $request->session()->put('error','code miss match');
            return back();
        }
    }

}
