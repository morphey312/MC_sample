<?php

Route::group([
    'prefix' => 'api/v1/notifications/mailing-template',
    'namespace' => 'App\V1\Http\Controllers\Notification',
    'middleware' => ['api', 'auth:api'],
], function() {
    Route::get('/', 'MailingTemplateController@list');
    Route::get('/{id}', 'MailingTemplateController@get');
    Route::post('/', 'MailingTemplateController@create');
    Route::put('/{id}', 'MailingTemplateController@update');
    Route::delete('/{id}', 'MailingTemplateController@delete');
    Route::get('/all', 'MailingTemplateController@all');
});

Route::group([
    'prefix' => 'api/v1/notifications/mailing-templates/mailing-settings/mailing-clinics',
    'namespace' => 'App\V1\Http\Controllers\Notification\MailingSetting',
    'middleware' => ['api', 'auth:api'],
], function() {
    Route::get('/', 'MailingClinicController@list');
    Route::get('/{id}', 'MailingClinicController@get');
    Route::post('/', 'MailingClinicController@create');
    Route::put('/{id}', 'MailingClinicController@update');
    Route::delete('/{id}', 'MailingClinicController@delete');
    Route::get('/all', 'MailingClinicController@all');
});
