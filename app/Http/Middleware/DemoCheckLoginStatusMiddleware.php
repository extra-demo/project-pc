<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

class DemoCheckLoginStatusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $uid = session()->get('uid');
        if (empty($uid)) {
            return $this->redirectTo($request);
        }

        $key = sprintf(config('oauth.oauth.cache_access_token_key'), $uid);

        /** @var AccessToken $accessToken */
        $accessToken = Cache::get($key);
        if (empty($accessToken)) {
            return $this->redirectTo($request);
        }

        if ($accessToken->hasExpired()) {
            /** @var GenericProvider $provider */
            $provider = app(GenericProvider::class);
            try {
                $newAccessToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $accessToken->getRefreshToken()
                ]);
                Cache::put($key, $newAccessToken, $newAccessToken->getExpires());
            } catch (IdentityProviderException $exception) {
                Cache::forget($key);
                return $this->redirectTo($request);
            }
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function redirectTo(Request $request)
    {
        return redirect('/login?return_url=' . $request->fullUrl());
    }
}
