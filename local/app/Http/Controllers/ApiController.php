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
use App\HotelAmities;
use App\HotelPermissions;

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
    public function getAmenties(Request $request){
      
      $user = DB::table('amenities')->get();
      return $user;
    }
    public function getHotelPermissioms(Request $request){
      
      $user = DB::table('hotels_permi')->get();
      return $user;
    }
    


    public function setHotelWizard(Request $request){
     

       
       
      

      try{

        $users = new Hotel;
        $users->name = $request->hotel_name;
        $users->hotel_address = $request->hotel_address;
        $users->city = $request->city;
        $users->no_rooms = $request->no_rooms;
        $users->no_resturant = $request->no_resturant;
        $users->railway_distance = $request->railway_distance;
        $users->bus_distance = $request->bus_distance;
       
        $users->save();
        $insertedId = $users->id;
        
       
        foreach ($request->hotel_ameties_data as $key => $value) {
          $users = new HotelAmities;
          $users->hotel_id = $insertedId;
          $users->amenties_id =$value;              
          $users->save();
           }

        foreach ($request->hotel_permission_data as $key => $value) {
        $users = new HotelPermissions;
        $users->hotel_id = $insertedId;
        $users->amenities_id =$value;              
        $users->save();
         }

        
        
        return $this->setSuccessResponse([],"Saved succesfully",$insertedId);

      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }


    }
    public function oderidCheck(Request $request){
      $user = DB::table('pay_trans')->where('orderid', $request->orderid)->first();
      if($user==null){
        $f=false;

      }else {
        $f=true;
      }
    return $this->setSuccessResponse($f,"get Transation Syatis",$user);
    }
    public function vpaytm(Request $request){
      header("Pragma: no-cache");
      header("Cache-Control: no-cache");
      header("Expires: 0");

      // following files need to be included
      require_once("pay/config_paytm.php");
      require_once("pay/encdec_paytm.php");

      $paytmChecksum = "";
      $paramList = array();
      $isValidChecksum = FALSE;

      $paramList = $_POST;
      $return_array = $_POST;
      $paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg

      //Verify all parameters received from Paytm pg to your application. Like MID received from paytm pg is same as your application’s MID, TXN_AMOUNT and ORDER_ID are same as what was sent by you to Paytm PG for initiating transaction etc.
      $isValidChecksum = verifychecksum_e($paramList, PAYTM_MERCHANT_KEY, $paytmChecksum); //will return TRUE or FALSE string.

      // if ($isValidChecksum===TRUE)
      // 	$return_array["IS_CHECKSUM_VALID"] = "Y";
      // else
      // 	$return_array["IS_CHECKSUM_VALID"] = "N";

      $return_array["IS_CHECKSUM_VALID"] = $isValidChecksum ? "Y" : "N";
      //$return_array["TXNTYPE"] = "";
      //$return_array["REFUNDAMT"] = "";
      unset($return_array["CHECKSUMHASH"]);

      $encoded_json = htmlentities(json_encode($return_array));
      $insertedId=DB::table('pay_trans')->insert(
        ['provider' => 'paytm',
        'orderid' => $_POST['ORDERID'],
        'data' => $encoded_json]
      );
      return $this->setSuccessResponse([],"Saved Transation",$insertedId);


    }
    public function gpaytm(Request $request){
      header("Pragma: no-cache");
      header("Cache-Control: no-cache");
      header("Expires: 0");
      // following files need to be included
      require_once("pay/config_paytm.php");
      require_once("pay/encdec_paytm.php");
      $checkSum = "";

      // below code snippet is mandatory, so that no one can use your checksumgeneration url for other purpose .
      $findme   = 'REFUND';
      $findmepipe = '|';

      $paramList = array();
      $orderid=$_POST['ORDER_ID'];
      $CUST_ID=$_POST['CUST_ID'];

      $paramList["MID"] = '';
      $paramList["ORDER_ID"] = $orderid;
      $paramList["CUST_ID"] =$CUST_ID;
      $paramList["INDUSTRY_TYPE_ID"] = '';
      $paramList["CHANNEL_ID"] = '';
      $paramList["TXN_AMOUNT"] = '';
      $paramList["WEBSITE"] = 'WEBSTAGING';

      foreach($_POST as $key=>$value)
      {
        $pos = strpos($value, $findme);
        $pospipe = strpos($value, $findmepipe);
        if ($pos === false || $pospipe === false)
          {
              $paramList[$key] = $value;
          }
      }



      //Here checksum string will return by getChecksumFromArray() function.
      $checkSum = getChecksumFromArray($paramList,PAYTM_MERCHANT_KEY);
      //print_r($_POST);
       echo json_encode(array("CHECKSUMHASH" => $checkSum,"ORDER_ID" => $orderid, "payt_STATUS" => "1"),JSON_UNESCAPED_SLASHES);
        //Sample response return to SDK

      //  {"CHECKSUMHASH":"GhAJV057opOCD3KJuVWesQ9pUxMtyUGLPAiIRtkEQXBeSws2hYvxaj7jRn33rTYGRLx2TosFkgReyCslu4OUj\/A85AvNC6E4wUP+CZnrBGM=","ORDER_ID":"asgasfgasfsdfhl7","payt_STATUS":"1"}



    }

    public function loginwithotp(Request $request){
          try{
            $credentials = $request->only('phone', 'device_id');
            $rules = [
                'phone' => 'required',
                'device_id' => 'required',
            ];
            $validator = Validator::make($credentials, $rules);

            if($validator->fails()) {
                throw new Exception('UserController-004');

            }
            $user_arr=User::where('phone',$request->phone)->where('device_id',$request->device_id)->first();
            if($user_arr===null){
              $user = User::create(['otp_verified'=>$request->otp_verified,'provider'=>'','device_id' =>$request->device_id,'phone' => $request->phone, 'email' => '', 'password' => Hash::make('44444')]);
                return $this->setSuccessResponse($user,"User List",'oo');

            }else{
               User::where('phone', $request->phone)
          ->where('device_id', $request->device_id)
          ->update(['otp_verified' => 1]);

              return $this->setSuccessResponse($user_arr,"User List",'oo');

            }

          }
          catch(JWTException $e){
              return response()->json(['success' => false, 'error' => 'Failed to login, please try again.'], 500);
          }
    }

    public function guard()
    {
      //  return Auth::guard();
    }
    //getProfile::
    public function getProfile(Request $request){
      try{


        $Reference = User::where('device_id',$request->device_id)
                      ->get();
        return $this->setSuccessResponse($Reference);
      }
      catch(\Exception $ex){
        return $this->setErrorResponse($ex->getMessage());
      }
    }
    //::getProfile
    //::roomBooking

    public function roomBooking(Request $request){
      try{

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

 $user_arr = Reference::where('device_id',$request->device_id)->orderBy('id', 'DESC')->get()->toArray();
        foreach ($user_arr as $key => $value) {
    if (is_null($value)) {
         $user_arr[$key] = "";
    }
}

       // $Reference = Reference::where('device_id',$request->device_id)                  ->get();

        return $this->setSuccessResponse($user_arr);
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
        $users->detail = $request->detail;
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
        $hotels = Room::where('hotal_id',$request->hotel_id)
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
