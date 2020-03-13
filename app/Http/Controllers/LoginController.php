<?php

namespace App\Http\Controllers;

use App\OAuthUtils;
use App\RestService\PassportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login(Request $request)
    {
        $config = config('oauth.authorization_code');
        $authorizationUrl = $this->provider->getAuthorizationUrl([]);

        Session::put(
            $config['cache_state_key'],
            $this->provider->getState()
        );

        return redirect($authorizationUrl)->withCookie(cookie('return_url'), $request->query('return_url', '/'), 10);
    }

    public function callback(Request $request)
    {
        $config = config('oauth.authorization_code');

        $error = $request->query('error');
        if ($error) {
            return $request->query();
        }

        $code = $request->query('code');
        if (empty($code)) {
            return redirect('/');
        }

        $state = Session::get($config['cache_state_key']);

        if ($state !== $request->query('state')) {
            return 'invalid state';
        }

        try {
            session()->put('cache_state_key', $this->provider->getState());

            $accessToken = $this->provider->getAccessToken('authorization_code', ['code' => $code]);

            $passport = new PassportService();
            $passport->setAccessToken($accessToken);
            $user = $passport->getOauthUser();
            session()->put('user', $user);
            session()->put('uid', $user['id']);

            Cache::put(
                sprintf(config('oauth.oauth.cache_access_token_key'), $user['id']),
                $accessToken,
                $accessToken->getExpires()
            );

            return redirect($request->cookie('return_url', '/'));
        } catch (IdentityProviderException $exception) {
            return $exception->getMessage();
        }
    }
}
