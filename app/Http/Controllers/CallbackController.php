<?php

namespace App\Http\Controllers;

use App\OAuthUtils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\GenericProvider;

class CallbackController extends Controller
{
    /**
     * @var \League\OAuth2\Client\Provider\GenericProvider
     */
    private $provider;

    public function __construct(GenericProvider $genericProvider)
    {
        $this->provider = $genericProvider;
    }

    public function webhook(Request $request, Response $response)
    {
        $user = $request->json('user');
//        $oauth = $request->json('oauth');
        Log::info(__METHOD__, $request->all());
        OAuthUtils::getInstance()->setUid($user['id'])->clearAccessToken();
        return OAuthUtils::getInstance()->setUid($user['id'])->getAccessToken();
    }
}
