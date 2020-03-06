<?php

return [
    'oauth' => [
        'client_id' => env('OAUTH_CLIENT_ID'),
        'clientSecret' => env('OAUTH_CLIENT_SECRET'),
        'redirectUri' => env("OAUTH_REDIRECT_URI"),
        'urlAuthorize' => env('OAUTH_URL_AUTHORIZE'),
        'urlAccessToken' => env('OAUTH_URL_ACCESSTOKEN'),
        'urlResourceOwnerDetails' => env('OAUTH_URL_RESOURCE_OWNER_DETAILS'),
        'cache_access_token_key' => 'cache_caess_token_key:%s',
    ],
    'authorization_code' => [
        'cache_state_key' =>  'cache_sate_key:%s',
        'cache_state_time' =>  60,
    ]
];
