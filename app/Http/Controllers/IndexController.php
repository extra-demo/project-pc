<?php

namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function showIndex()
    {
        $accessToken = \Illuminate\Support\Facades\Cache::get(LOGIN_STATUS_KEY);
        return $accessToken;
    }
}
