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
        $config = config('oauth.authorization_code');
        $authorizationUrl = $this->provider->getAuthorizationUrl([]);

        Cache::put(
            sprintf($config['cache_state_key'], 'id=1'),
            $this->provider->getState(),
            $config['cache_state_time']

        );

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
            Cache::put(
                sprintf(config('oauth.oauth.cache_access_token_key'), 'id=1'),
                $accessToken,
                $accessToken->getExpires()
            );

            return redirect($request->cookie('return_url', '/'));
        } catch (IdentityProviderException $exception) {
            return $exception->getMessage();
        }
    }
}
