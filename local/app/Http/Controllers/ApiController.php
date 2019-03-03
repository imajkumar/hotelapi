<?php

namespace App\Http\Controllers;

use DB;
use App\User;
use App\Offer;
use App\Location;
use App\Hotel;
use App\GuestUser;
use App\Reference;
use App\BookRoom;
use App\Room;

use App\forgetPasswords;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Mail\Message;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\PayloadFactory;
use Exception;
class ApiController extends Controller
{

    public function __construct()
    {
      //  $this->middleware('auth:api', ['except' => ['getOffer']]);
    }

    public function guard()
    {
      //  return Auth::guard();
    }
    //::roomBooking

    public function roomBooking(Request $request){
      try{
        echo "string";

        $users = new BookRoom;
        $users->hotel_id = $request->hotel_id;
        $users->room_id = $request->room_id;
        $users->device_id = $request->device_id;
        $users->user_id = $request->user_id;
        $users->check_in = $request->check_in;
        $users->check_out = $request->check_out;
        $users->adult = $request->adult;
        $users->child = $request->child;
        $users->infant = $request->infant;
        $users->offer_cpde = $request->offer_code;
        $users->booking_type =1;
        $users->save();
        $insertedId = $users->id;
        Room::where('hotal_id',  $request->hotel_id)
          ->where('room_id', $request->room_id)
          ->update(['status' => 2]);

        return $this->setSuccessResponse([],"Booked Saved succesfully",$insertedId);

      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }


    //roomBooking::

    //::getReference
    public function getReference(Request $request){
      try{


        $Reference = Reference::where('device_id',$request->device_id)
                      ->get();
        return $this->setSuccessResponse($Reference);
      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }
    //getReference::
    //::SetReference
    public function SetReference(Request $request){
      try{
        $users = new Reference;
        $users->device_id = $request->device_id;
        $users->reference_no = $request->reference_no;
        $users->reference_type = $request->reference_type;
        $users->save();
        $insertedId = $users->id;
        return $this->setSuccessResponse([],"Reference Saved succesfully",$insertedId);

      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }

    //SetReference::
    //::rooms
    public function getRooms(Request $request){
      try{
        $hotels = Hotel::where('hotal_id',$request->hotel_id)
                      ->get();
        return $this->setSuccessResponse($hotels);
      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }
    //rooms::
    //::getHotels
    public function getHotels(Request $request){
      try{
        $hotels = Hotel::all();
        return $this->setSuccessResponse($hotels);
      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }
    //getHotels::
    //::getGuest
    public function getGuest(Request $request){
      try{
        $guestuser = GuestUser::where('token',$request->token)->first();
        $data = array(
          'token' =>(!isset($guestuser->token) || is_null($guestuser->token)) ? '' : $guestuser->token,
          'email' =>(!isset($guestuser->email) || is_null($guestuser->email)) ? '' : $guestuser->email,
          'phone' =>(!isset($guestuser->phone) || is_null($guestuser->phone)) ? '' : $guestuser->phone,

        );
        return $this->setSuccessResponse($data);
      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }

    //getGuest::
    //::guest
    public function guest(Request $request)
    {
      try{
        $credentials = $request->only('device_id');

        $rules = [
            'device_id' => 'required|unique:guest_user'

        ];
        $validator = Validator::make($credentials, $rules);

        if($validator->fails()) {
            //throw new Exception('UserController-001','44');
            return $this->setErrorResponse($validator->messages());

        }
          $token =  uniqid(base64_encode(str_random(60)));

        $users = new GuestUser;
        $users->device_id = $request->device_id;
        $users->token = $token;
        $users->save();
        $insertedId = $users->id;
        return $this->setSuccessResponse([],"SUCCESS-LOGIN",$token);

      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }


        // all good so return the token

    }
    //guest::
    //::getHotel
    public function getHotel(Request $request){
      try{
        $hotels = Hotel::where('location_id',$request->location_id)
                      ->get();
        return $this->setSuccessResponse($hotels);
      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }
    //getHotel::
    //::getLocation

    public function getLocation(Request $request){
      try{
        $search = $request->s;

        $locatins = Location::where('display_name','LIKE',"%{$search}%")
                      ->get();
        return $this->setSuccessResponse($locatins);
      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }

    //getLocation::
    //::get Offer
    public function getOffer(Request $request){
      try{
        $bus_offer=Offer::where('offer_type',0)->get();
        $flight_offer=Offer::where('offer_type',1)->get();
        $hotel_offer=Offer::where('offer_type',2)->get();
        $cab_offer=Offer::where('offer_type',2)->get();
        $data_ = array(
          'bus' => $bus_offer,
          'flight' => $flight_offer,
          'hotel' => $hotel_offer

        );
        return $this->setSuccessResponse($data_);
      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }
    //get Offer ::




}
