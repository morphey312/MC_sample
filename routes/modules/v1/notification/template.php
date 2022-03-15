<?php

Route::group([
    'prefix' => 'api/v1/notifications/templates',
    'namespace' => 'App\V1\Http\Controllers\Notification',
    'middleware' => ['api', 'auth:api'],
], function() {
    Route::get('/', 'TemplateController@list');
    Route::get('/{id}', 'TemplateController@get');
    Route::post('/', 'TemplateController@create');
    Route::put('/{id}', 'TemplateController@update');
    Route::delete('/{id}', 'TemplateController@delete');
    Route::get('/all', 'TemplateController@all');
});

Route::group([
    'prefix' => 'api/v1/notifications/templates/settings/clinics',
    'namespace' => 'App\V1\Http\Controllers\Notification\Setting',
    'middleware' => ['api', 'auth:api'],
], function() {
    Route::get('/', 'ClinicController@list');
    Route::get('/{id}', 'ClinicController@get');
    Route::post('/', 'ClinicController@create');
    Route::put('/{id}', 'ClinicController@update');
    Route::delete('/{id}', 'ClinicController@delete');
    Route::get('/all', 'ClinicController@all');
});
