<?php

namespace App\Http\Controllers;

use App\OAuthUtils;
use App\RestService\PassportService;

class IndexController extends Controller
{
    public function showIndex()
    {
        $accessToken = OAuthUtils::getInstance()->getAccessToken();
        return
            [
                'uid' => session('uid'),
                'user' => session('user'),
                'login' => route('login'),
                'info' => route('info'),
                'logout' => 'http://127.0.0.1:9501/logout?' . http_build_query([
                        's' => urlencode(route('logout')),
                        'from' => 'project-pc',
                        'access_token' => $accessToken ? $accessToken->getToken(): "",
                    ]),
            ];
    }

    public function info(PassportService $passportService)
    {
        $accessToken = OAuthUtils::getInstance()->getAccessToken();
        return [
            'access_token' => $accessToken,
            'remote_user' => $passportService->getOauthUser(),
        ];
    }
}
