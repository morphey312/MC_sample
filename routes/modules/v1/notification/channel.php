<?php

Route::group([
    'prefix' => 'api/v1/notifications/channels',
    'namespace' => 'App\V1\Http\Controllers\Notification',
    'middleware' => ['api', 'auth:api'],
], function() {
    Route::get('/', 'ChannelController@list');
    Route::get('/{id}', 'ChannelController@get');
    Route::post('/', 'ChannelController@create');
    Route::put('/{id}', 'ChannelController@update');
    Route::delete('/{id}', 'ChannelController@delete');
    Route::get('/all', 'ChannelController@all');
});