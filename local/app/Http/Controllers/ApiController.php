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
        $offer=Offer::get();
        return $this->setSuccessResponse($offer);
      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }
    //get Offer ::




}
