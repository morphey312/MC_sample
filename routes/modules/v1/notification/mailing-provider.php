<?php

Route::group([
    'prefix' => 'api/v1/notifications/mailing-provider',
    'namespace' => 'App\V1\Http\Controllers\Notification',
    'middleware' => ['api', 'auth:api'],
], function() {
    Route::get('/', 'MailingProviderController@list');
    Route::get('/{id}', 'MailingProviderController@get');
    Route::post('/', 'MailingProviderController@create');
    Route::put('/{id}', 'MailingProviderController@update');
    Route::delete('/{id}', 'MailingProviderController@delete');
    Route::get('/all', 'MailingProviderController@all');
});

