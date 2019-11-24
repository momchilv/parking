<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

//Route::get('/', function () {
//    return view('parking');
//});

Route::post('login','ApiController@login');
Route::get('check-free-places','ApiController@checkFreePlaces')->name('check-free-places')->middleware('auth');
Route::get('check-car','ApiController@checkCar')->name('check-car')->middleware('auth');
Route::post('check-in-car','ApiController@checkInCar')->name('check-in-car')->middleware('auth');
Route::post('check-out-car','ApiController@checkOutCar')->name('check-out-car')->middleware('auth');
