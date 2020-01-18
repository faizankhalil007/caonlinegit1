<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:11', 'min:7', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */

    public function CustomerPushNotification($data)
    {
        $headers = array
        (
            'Authorization: key=AAAAV9Mf4wo:APA91bEB_q0NbcDqZMPpGPYDg4DvfBkbBv7vldds2WgOSYEXnEP_cmoynGlVd2BkvH7jALa1ebA5-U6ZWD72fWLaNtfo_Ib-1E3-oq41V-U2TgR82mn-Z2CC41uNq7MksTrd9RtpKpJu',
            'Content-Type: application/json'
        );
        $fields = array
        (
            'to'            => '/topics/SmsSend',
            'data'          => $data,
            "priority"      => "high"
        );
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
        // curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true );
        // curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );

        $result = curl_exec($ch);
        if ($result === FALSE)

        {
            die('FCM Send Error: ' . curl_error($ch));
        }

        // echo $result;
        curl_close($ch);
        return $ch;
    }
    protected function create(array $data)
    {
        $varification_code = rand(1000,9999);
        $push_data = array(
            'title' =>  $data['phone_number'],
            'body'  =>  'Your CA Online Test Verification Code is '.$varification_code,
        );
        $this->CustomerPushNotification($push_data);
//        print_r($curl_result); exit;
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'user_type' => $data['user_type'],
            'verification_code' =>  $varification_code,
            'password' => Hash::make($data['password']),
            'temp_pass' => $data['password'],
        ]);
    }
}
