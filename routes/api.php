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

Route::prefix('v1')->group(function () {
    Route::group([
        'prefix' => 'auth',
    ], function() {
        Route::middleware('api')->post('login', 'AuthController@login');
        Route::middleware('api')->post('patient-login', 'AuthController@patientLogin');
        Route::middleware('api')->post('patient-password', 'AuthController@patientChangePassword');
        Route::middleware('api')->post('staff-login', 'AuthController@staffLogin');
        Route::middleware('auth:api')->get('logout', 'AuthController@logout');
        Route::middleware(['auth:api', 'token.refresh'])->get('refresh', 'AuthController@refresh');
        Route::middleware('auth:api')->post('patient-phone/check', 'AuthController@patientVerifyPhone');
        Route::middleware('auth:api')->post('patient-password/recover', 'AuthController@patientRecoverPassword');
    });

    Route::group([
        'prefix' => 'maintenance',
    ], function() {
        Route::middleware('api')->get('server-time', 'MaintenanceController@serverTime');
        Route::middleware('api')->get('ping', 'MaintenanceController@ping');
    });

    Route::group([
        'middleware' => ['auth:api'],
    ], function() {
        Route::group([
            'prefix' => 'session-logs',
        ], function() {
            Route::post('/', 'SessionLogController@create');
        });

        Route::group([
            'prefix' => 'voip',
        ], function() {
            Route::get('/counters', 'VoipController@counters');
        });
    });

    Route::group([
        'middleware' => ['auth:api'],
    ], function() {
        Route::group([
            'prefix' => 'promise',
        ], function() {
            Route::get('/{promise}', 'PromiseController@get');
        });
    });

    Route::group([
        'middleware' => ['auth:api'],
    ], function() {
        Route::group([
            'prefix' => 'mc-lab',
        ], function() {
            Route::get('/{list}', 'Analysis\Laboratory\ProxyController@getList');
        });
    });
});
