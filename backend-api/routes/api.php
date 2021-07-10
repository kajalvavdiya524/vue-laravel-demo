<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['auth:sanctum'], 'namespace' => 'Api'], function () {

  Route::group(['prefix' => 'user'], function () {
    Route::get('', 'UserController@get');
    Route::put('', 'UserController@update')->middleware('permission:profile');
    Route::post('profile', 'UserController@setProfile');

    Route::group(['prefix' => 'hotels', 'middleware' => 'permission:hotels'], function () {
      Route::get('', 'HotelsController@list');
      Route::get('{hotel}', 'HotelsController@get');
      Route::get('{hotel}/pages', 'HotelsController@getPages');
      Route::post('', 'HotelsController@create');
    });

    Route::get('group', 'GroupsController@show');//->middleware('permission:group');
    Route::put('group/{group}', 'GroupsController@update');//->middleware('permission:group');
  });

  Route::group(['prefix' => 'data'], function () {
    Route::get('countries', 'DataController@getCountries');
    Route::get('currencies', 'DataController@getCurrencies');
    Route::get('pages', 'DataController@getPages');
  });

  Route::group(['middleware' => ['hotel.check']], function () {

    Route::group(['prefix' => 'user'], function () {
      Route::patch('setup', 'HotelsController@updateSetupStep');
    });

    Route::group(['middleware' => ['permission:booking'], 'prefix' => 'logs/hotels'], function () {
      Route::get('{hotel}/status', 'HotelsController@listStatusLogs');
    });

    Route::group(['prefix' => 'user/hotels'], function () {
      Route::post('{hotel}', 'HotelsController@update')->middleware('permission:hotels');
      Route::patch('{hotel}', 'HotelsController@toggleStatus')->middleware('permission:booking,hotels');
    });

    Route::group(['middleware' => ['permission:reservations'], 'prefix' => 'reservations'], function () {
      Route::post('', 'ReservationsController@get');
      Route::patch('cancel', 'ReservationsController@cancel');
    });

    Route::group(['middleware' => ['permission:masterdata,description'], 'prefix' => 'description'], function () {
      Route::get('', 'DescriptionController@get');
      Route::put('', 'DescriptionController@update');
    });

    Route::group(['middleware' => ['permission:nearby'], 'prefix' => 'nearby'], function () {
      Route::get('', 'NearByController@get');
      Route::put('', 'NearByController@update');
    });

    Route::group(['middleware' => ['permission:facilities'], 'prefix' => 'facilities'], function () {
      Route::get('', 'FacilitiesController@get');
      Route::put('', 'FacilitiesController@update');
    });

    Route::group(['middleware' => ['permission:calendar'], 'prefix' => 'calendar'], function () {
      Route::get('rooms', 'CalendarController@getRooms');
      Route::post('rooms', 'CalendarController@getRoomsData');
      Route::put('rooms/avail', 'CalendarController@updateRoomAvail');
      Route::put('rooms/data', 'CalendarController@updateRoomData');
      Route::put('rooms/batch', 'CalendarController@batchUpdateRooms');
    });

    Route::group(['middleware' => ['permission:rateplans'], 'prefix' => 'plans'], function () {
      Route::get('', 'PlansController@get');
      Route::post('', 'PlansController@create');
      Route::put('{id}', 'PlansController@update');
      Route::post('{id}/duplicate', 'PlansController@duplicate');
      Route::delete('{id}', 'PlansController@destroy');
    });

    Route::group(['middleware' => ['permission:contactpersons'], 'prefix' => 'contacts'], function () {
      Route::get('', 'ContactsController@get');
      Route::post('', 'ContactsController@create');
      Route::put('{id}', 'ContactsController@update');
      Route::delete('{id}', 'ContactsController@destroy');
    });

    Route::group(['middleware' => ['permission:roomtypes'], 'prefix' => 'rooms'], function () {
      Route::get('', 'RoomsController@get');
      Route::post('', 'RoomsController@create');
      Route::put('{id}', 'RoomsController@update');
      Route::post('{id}', 'RoomsController@duplicate');
      Route::delete('{id}', 'RoomsController@destroy');
    });

    Route::group(['middleware' => ['permission:mealplans'], 'prefix' => 'mealplans'], function () {
      Route::get('', 'MealPlansController@get');
      Route::post('', 'MealPlansController@create');
      Route::put('{id}', 'MealPlansController@update');
      Route::post('{id}', 'MealPlansController@duplicate');
      Route::delete('{id}', 'MealPlansController@destroy');
    });

    Route::group(['middleware' => ['permission:photos'], 'prefix' => 'images'], function () {
      Route::get('', 'ImagesController@get');
      Route::post('', 'ImagesController@create');
      Route::delete('{image}', 'ImagesController@destroy');
      Route::put('{image}', 'ImagesController@update');
      Route::patch('{room}', 'ImagesController@reorder');
    });

    Route::group(['middleware' => ['permission:policies'], 'prefix' => 'policies'], function () {
      Route::get('', 'PoliciesController@get');
      Route::post('cancel', 'PoliciesController@createCancel');
      Route::put('cancel/{id}', 'PoliciesController@updateCancel');
  //    Route::post('cancel/{id}/duplicate', 'PoliciesController@duplicateCancel');
      Route::delete('cancel/{id}', 'PoliciesController@destroyCancel');
      Route::post('payment', 'PoliciesController@createPayment');
      Route::put('payment/{id}', 'PoliciesController@updatePayment');
  //    Route::post('payment/{id}/duplicate', 'PoliciesController@duplicatePayment');
      Route::delete('payment/{id}', 'PoliciesController@destroyPayment');
    });

    Route::group(['middleware' => ['permission:channels'], 'prefix' => 'channels'], function () {
      Route::get('', 'ChannelsController@list');
      Route::get('{id}/fields', 'ChannelsController@getFields');
      Route::get('{id}', 'ChannelsController@get');
      Route::patch('{id}', 'ChannelsController@state');
      Route::put('{id}', 'ChannelsController@mappings');
      Route::group(['prefix' => '{id}/{mode}', 'where' => ['mode' => 'promo|contract']], function () {
        Route::post('', 'ChannelsController@createPromo');
        Route::put('{item}', 'ChannelsController@updatePromo');
        Route::delete('{item}', 'ChannelsController@deletePromo');
      });
    });

    Route::group(['middleware' => ['permission:invoices'], 'prefix' => 'invoices'], function () {
      Route::post('', 'InvoicesController@get');
    });

    Route::group(['prefix' => 'reports'], function () {
      Route::get('recent', 'ReportsController@getRecent');
    });

    Route::group(['middleware' => ['permission:systems'], 'prefix' => 'systems'], function () {
      Route::get('', 'SystemsController@all');
      Route::post('', 'SystemsController@state');
    });

    Route::group(['prefix' => 'directus'], function () {
      Route::group(['middleware' => ['permission:legal'], 'prefix' => 'pages'], function () {
        Route::get('/{id}', 'Directus\PageController@show');
      });
    });
  });

  /**
   * @OA\Info(title="Extranet auth API", version="0.1")
   */
  Route::namespace('Users')->group(function () {
    Route::post('users/invite', 'UsersController@createInviteUser')->middleware(['hotel.check:0', 'permission:users']);
    Route::apiResource('roles', 'RolesController', ['except' => 'show'])->middleware(['hotel.check:0', 'permission:users']);
    Route::apiResource('users', 'UsersController', ['except' => 'show'])->middleware(['hotel.check:0', 'permission:users']);
  });

  Route::group(['prefix' => 'admin', 'middleware' => 'admin.check'], function () {
    Route::apiResource('groups', 'GroupsController');
    Route::group(['prefix' => 'groups/{group}/hotels'], function () {
      Route::post('import/{id}', 'HotelsController@import');
      Route::get('{hotel}', 'HotelsController@get');
      Route::post('{hotel}', 'HotelsController@update');
      Route::post('', 'HotelsController@create');
      Route::patch('{hotel}', 'HotelsController@toggleStatus');
    });
    Route::post('groups/{group}/hotels', 'HotelsController@create');
  });

});


