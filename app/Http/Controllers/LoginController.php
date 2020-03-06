<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

class LoginController extends Controller
{
    /**
     * @var \League\OAuth2\Client\Provider\GenericProvider
     */
    private $provider;

    public function __construct(GenericProvider $genericProvider)
    {
        $this->provider = $genericProvider;
    }

    public function login(Request $request, Response $response)
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl([]);

        Cache::put(LOGIN_STATUS_STATE, $this->provider->getState(), new \DateInterval("PT1M"));

        return redirect($authorizationUrl)->withCookie(cookie('return_url'), $request->query('return_url', '/'), 10);
    }

    public function callback(Request $request, Response $response)
    {

        $error = $request->query('error');
        if ($error) {
            return $request->query();
        }

        $code = $request->query('code');
        if (empty($code)) {
            return redirect('/');
        }
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', ['code' => $code]);
            Cache::put(LOGIN_STATUS_KEY, $accessToken,  new \DateInterval("PT1H"));

            return $accessToken;
        } catch (IdentityProviderException $exception) {
            return $exception->getMessage();
        }
    }
}
