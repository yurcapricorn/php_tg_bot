<?php


//use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});
Route::get('test', 'TestController@run');
Route::post('/bot_webhook', 'BotController@run');
Route::get('/bot_webhook', 'BotController@run');

Route::get('/users', 'UserController@getAll');
Route::get('/objects', 'ObjectController@getAll');
Route::get('/bookings', 'BookingController@getAll');