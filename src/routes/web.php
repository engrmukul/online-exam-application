<?php

use Illuminate\Support\Facades\Route;

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


Route::group(['middleware' => ['auth']], function () {
    //RESET PASSWORD
    Route::get('/change-password', 'HomeController@changePassword')->name('change-password');
    Route::put('/reset-password', 'HomeController@resetPassword')->name('reset-password');

    Route::resource('users', 'UserController', ['except' => ['show']]);

    Route::resource('subjects', 'SubjectController', ['except' => ['show']]);
    Route::get('subjects/get-data', 'SubjectController@getData');
    Route::get('subjects/restore', 'SubjectController@restore')->name('subjects.restore');

});


Auth::routes();

Route::get('/', 'HomeController@index')->name('home');


//FALLBACK ROUTE
Route::fallback(function () {
    return response()->view('error.404');
});
