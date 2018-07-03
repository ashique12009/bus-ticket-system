<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use DB;

class BusAuthController extends Controller
{
    public function showRegistrationForm()
    {
    	return view('registration');
    }

    public function register(Request $request)
    {
    	$this->registration_validation($request);
    	User::create($request->all());
    	return redirect('/')->with('Status', 'Registration complete, You can login now.');
    }

    public function registration_validation($request)
    {
        $rules = [
            'fname' => 'required|max:50',
            'lname' => 'required|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|max:200'
        ];

        $customMessages = [
            'fname.required' => 'First name cannot be blank!',
            'lname.required' => 'Last name cannot be blank!',
            'email.required' => 'Email cannot be blank!'
        ];

    	return $this->validate($request, $rules, $customMessages);
    }

    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $this->login_validation($request);
        if ( auth()->attempt(request(['email', 'password'])) == false ) {
            return back()->withErrors(['messages' => 'Invalid email or password.']);
        }
        
        $user_info = auth()->user();
        $first_name = $user_info->fname;
        $status = 'Welcome Customer';
        $data = ['fname' => $first_name, 'Status' => $status];
        return redirect()->to('customer-welcome')->with($data);
    }

    public function login_validation($request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        return $this->validate($request, $rules);
    }

    public function logout()
    {
        auth()->logout();
        return redirect()->to('login-form');
    }

    // Admin login logout

    public function showAdminLoginForm()
    {
        return view('admin.admin-login');
    }

    public function adminLogin(Request $request)
    {
        $email      = $request->get('email');
        $password   = $request->get('password');
        $credentials = [
            'email' => $email,
            'password' => $password
        ];

        if ( auth()->attempt($credentials) ) {
            $user_info  = DB::table('users')
                        ->where('email', $email)
                        ->get();
            if ( $user_info[0]->admin == 1 ) {
                return redirect()->to('admin/dashboard');
            }
        }

        return redirect()->to('admin/login');
    }

    public function adminLogout()
    {
        auth()->logout();
        return redirect()->to('admin/login');
    }
}
