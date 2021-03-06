<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->get('user/verify/{verification_code}', 'AuthController@verifyUser');

$router->post('register', 'AuthController@register');
$router->post('guest', 'ApiController@guest');  //unknown user
$router->post('getGuest', 'ApiController@getGuest');  //unknown user

$router->post('getoffer', 'ApiController@getOffer');
$router->post('hotels', 'ApiController@getHotels');
$router->post('rooms', 'ApiController@rooms');
$router->post('getRoom', 'ApiController@getRooms');

$router->post('getLocation', 'ApiController@getLocation');
$router->post('getHotel', 'ApiController@getHotel');
$router->post('roomBooking', 'ApiController@roomBooking');

$router->post('SetReference', 'ApiController@SetReference');
$router->post('getReference', 'ApiController@getReference');
$router->post('getProfile', 'ApiController@getProfile');


$router->post('login', 'AuthController@login');
$router->post('loginwithotp', 'ApiController@loginwithotp');
$router->post('gpaytm', 'ApiController@gpaytm');
$router->post('vpaytm', 'ApiController@vpaytm');
$router->post('oderidCheck', 'ApiController@oderidCheck');
$router->post('setHotelWizard', 'ApiController@setHotelWizard');
$router->post('getAmenties', 'ApiController@getAmenties');
$router->post('getHotelPermissioms', 'ApiController@getHotelPermissioms');




$router->post('recover-request', 'AuthController@recoverRequest');
$router->post('verify-temp-password', 'AuthController@verifyTempPassword');
$router->post('set-new-password', 'AuthController@setNewPassword');

Route::group(['middleware' => ['jwt.auth']], function() {
    Route::get('logout', 'AuthController@logout');

    Route::get('allUser', 'TestController@allUser');

});
