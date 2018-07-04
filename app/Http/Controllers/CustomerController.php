<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use Session;
use DB;

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
            return redirect('edit-profile-form');
        }
    }

    public function showBusList()
    {
        $bus_info = DB::table('buses')->get();
        return view('customer.customer-bus-list', ['bus_info' => $bus_info]);
    }

    public static function getAvailableSeat($id)
    {
        $bus_info = DB::table('buses')->where('id', $id)->get();
        $total_seat = $bus_info[0]->total_seat;
        $booked_seat = DB::table('booking')->where('bus_id', $id)->count();
        return $total_seat - $booked_seat;
    }

    public function showBusSeatDetail($id)
    {
        $bus_info = DB::table('buses')->where('id', $id)->get();
        //Make array for available seat numbers
        $booking_info = DB::table('booking')->where('bus_id', $id)->where('status', 1)->get();
        
        $busy_seat_array = [];
        if ( count($booking_info) > 0 ) {
            foreach ( $booking_info as $value ) {
                array_push($busy_seat_array, $value->seat_no);
            }
        }
        
        return view('customer.booking-form', ['bus_info' => $bus_info, 'busy_seats' => $busy_seat_array]);
    }

    public function bookingNow(Request $request)
    {
        $this->booking_validation($request);
        $user_id = auth()->user()->id;
        $bus_id = $request->get('bus_id');
        $seat_no = $request->get('seat_no');

        //Check this seat is free in this bus or not
        $check = DB::table('booking')
                    ->where('bus_id', $bus_id)
                    ->where('seat_no', $seat_no)
                    ->where('status', 1)
                    ->count();
        if ( $check == 0 ) {
            $insertData = [
                'user_id'       => $user_id,
                'bus_id'        => $bus_id,
                'seat_no'       => $seat_no,
                'status'        => 1,
                'created_at'    => \Carbon\Carbon::now(),
                'updated_at'    => \Carbon\Carbon::now()
            ];
            DB::table('booking')->insert($insertData);
            Session::flash('msg', 'Seat Booking has been done successfully');
            return redirect('show-bus-list');
        } 
        else {
            Session::flash('err-msg', 'This Seat is Unavailable');
            return redirect('show-bus-list');
        }
    }

    public function booking_validation($request)
    {
        $rules = [
            'seat_no' => 'required|numeric'
        ];

        $custom_message = [
            'seat_no.required' => 'Please input your seat number'
        ];

        return $this->validate($request, $rules, $custom_message);
    }

}
