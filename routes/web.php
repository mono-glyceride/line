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

Route::get('/linelogin', 'LineLoginController@lineLogin')->name('linelogin');
Route::get('/callback', 'LineLoginController@callback')->name('callback');
Route::POST('/callback', 'LineLoginController@clickBtn')->name('clickBtn');
Route::get('/', function () {
    return view('welcome');
});
