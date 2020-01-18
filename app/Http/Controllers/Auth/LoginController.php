<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    /*protected $redirectTo = '/student/dashboard';
    protected function redirectTo()
    {
        if (auth()->user()->user_type == 4){
//            return view('student/dashboard');
            $this->redirectTo = '/student/dashboard';
        } elseif (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 || auth()->user()->user_type == 3){
            return view('admin/dashboard');
        } else{
            redirect('logout');
        }

    }*/
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
