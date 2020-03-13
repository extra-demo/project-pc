<?php

namespace App\Http\Controllers;

use App\OAuthUtils;

class IndexController extends Controller
{
    public function showIndex()
    {
        return
            [

                'uid' => session('uid'),
                'user' => session('user'),
                'login' => route('login'),
                'info' => route('info'),
                'logout' => route('logout'),
            ];
    }

    public function info()
    {
        $accessToken = OAuthUtils::getInstance()->getAccessToken();
        return [
            'access_token' => $accessToken,
        ];
    }
}
