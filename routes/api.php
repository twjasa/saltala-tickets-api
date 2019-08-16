<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//PUBLIC ROUTES
Route::prefix('user')->group(function () {
    Route::post('login', 'UserController@login');
    Route::post('signup', 'UserController@signup');
});

/// MIDDLEWARE
Route::group(['middleware' => 'auth:api'], function () {
    Route::prefix('ticket')->group(function () {
        Route::post('/update/{ticket_id}', 'TicketController@update');
    });
    //USER ROUTES
    Route::get('user/logout', 'UserController@logout');
    Route::group(['middleware'=>'scope:user'], function() {
        Route::post('/new-ticket/{user_id}', 'TicketController@newTicket');
        Route::get('/get-user-tickets/{user_id}', 'TicketController@getUserTickets');
    });
    ///ADMIN ROUTES
    Route::group(['middleware' => 'scope:admin'], function () {
        Route::delete('delete/{ticket_id}', 'TicketController@destroy');
        Route::get('get-all-tickets', 'TicketController@allTickets');
        Route::post('update-ticket/{ticket_id}', 'TicketController@update');
    });
});
