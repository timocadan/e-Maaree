<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
     * Central (landlord) domain → dashboard; tenant domain → /home.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Redirect path after login (central vs tenant).
     */
    public function redirectTo()
    {
        $centralDomains = config('tenancy.central_domains', ['localhost', '127.0.0.1']);
        if (in_array(request()->getHost(), $centralDomains)) {
            return '/dashboard';
        }
        return $this->redirectTo;
    }

    /**
     * Create a new controller instance.
     *
     * @return void$field
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /*
     *  Login with Username or Email
     * */
    public function username()
    {
        $identity = request()->identity;
        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $identity]);
        return $field;
    }
}
