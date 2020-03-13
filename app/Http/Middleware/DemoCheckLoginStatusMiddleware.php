<?php

namespace App\Http\Middleware;

use App\OAuthUtils;
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
        // 初始化数据
        // 1 小时后，access_token 过期， 刷新 access_token， 更新本地 session 到期时间，
        if (OAuthUtils::getInstance()->getAccessToken() === null) {
            return $this->redirectTo($request);
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
