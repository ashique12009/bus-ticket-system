<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use Session;

class CustomerController extends Controller
{
	public function __construct()
	{
		$this->middleware('authuser');
	}

    public function index()
    {
    	return view('customer.customer-welcome');
    }

    public function profile()
    {
        $user_id = auth()->user()->id;
        $customer_info = Customer::where('user_id', $user_id)->get();
    	return view('customer.customer-profile', ['customer_info' => $customer_info]);
    }

    public function showEditProfileForm()
    {
        $user_id = auth()->user()->id;
        $customer_info = Customer::where('user_id', $user_id)->get();
        return view('customer.edit-profile-form', ['customer_info' => $customer_info]);
    }

    public function updateProfileInfo(Request $request)
    {
        $user_id = auth()->user()->id;
        $customer_info = Customer::where('user_id', $user_id)->get();
        if ( count($customer_info) > 0 ) {
            $address = $request->get('address');
            $phone = $request->get('phone');
            if ( $request->hasFile('pp') ) {
                $fileName = $user_id . '-' . $request->file('pp')->getClientOriginalName();
                $destinationPath = public_path('/images');
                $request->file('pp')->move($destinationPath, $fileName);
                // Now update the database table only photo field
                $update_data = [
                    'photo' => $fileName
                ];
                Customer::where('user_id', $user_id)->update($update_data);
            }
            $update_data = [
                'address'   => $address,
                'phone'     => $phone
            ];
            Customer::where('user_id', $user_id)->update($update_data);
            Session::flash('msg', 'Customer information updated successfully');
            // return redirect('edit-profile-form')->with('msg', 'Customer information updated successfully');
            return redirect('edit-profile-form');
        }
    }

    public function showBookingForm()
    {
        return view('booking-form');
    }

}
