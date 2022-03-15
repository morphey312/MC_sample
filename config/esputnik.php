<?php

return [
    'base_url' => env('ESPUTNIK_BASE_URL','https://esputnik.com.ua/api/v1/'),
    'timeout' => 15,
    'retries' => 3,
    'auth' => [
        'login' => env('ESPUTNIK_LOGIN', 'api1.es@onclinic.ua'),
        'password' => env('ESPUTNIK_PASSWORD', '1ofyv4XLyUTe')
    ],
    //in debug mode create records in the logs
    'debug' => env('ESPUTNIK_DEBUG', 'true'),
    // permission to send messages
    'send' => env('ESPUTNIK_SEND', 'true')
];
