<?php

namespace App\Http\Controllers;

use DB;
use App\User;
use App\Offer;
use App\Location;
use App\Hotel;


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

    //::guest
    public function guest(Request $request)
    {
      try{
        $credentials = $request->only('device_id');

        $rules = [
            'device_id' => 'required|unique:users'

        ];
        $validator = Validator::make($credentials, $rules);

        if($validator->fails()) {
            //throw new Exception('UserController-001','44');
            return $this->setErrorResponse($validator->messages());

        }
        $users = new User;
        $users->device_id = $request->device_id;
        $users->save();
        $insertedId = $users->id;
        $user = User::find($insertedId);

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::fromUser($user))
            {
                return response()->json(['success' => false, 'error' => 'We cant find an account with this credentials. Please make sure you entered the right information and you have verified your email address.'], 404);
            }
        } catch (JWTException $e)
        {
            // something went wrong whilst attempting to encode the token
            return response()->json(['success' => false, 'error' => 'Failed to login, please try again.'], 500);
        }

      //  $token = $this->respondWithToken($token);
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
