<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
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
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        /** @var AccessToken $accessToken */
        $accessToken = Cache::get(LOGIN_STATUS_KEY);
        if (empty($accessToken)) {
            return redirect('/login?return_url=' . $request->fullUrl());
        }

        if ($accessToken->hasExpired()) {
            /** @var GenericProvider $provider */
            $provider = app(GenericProvider::class);
            $newAccessToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $accessToken->getRefreshToken()
            ]);
            Cache::put(LOGIN_STATUS_KEY, $newAccessToken, new \DateInterval("PT1H"));
        }

        return $next($request);
    }
}
