<?php

namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function showIndex()
    {
        $key = sprintf(
            config('oauth.oauth.cache_access_token_key'),
            "id=1"
        );
        $accessToken = \Illuminate\Support\Facades\Cache::get($key);
        return $accessToken;
    }
}
