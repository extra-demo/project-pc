<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use League\OAuth2\Client\Provider\GenericProvider;

class OAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(GenericProvider::class, function () {
            $config = config('oauth.oauth');
            return new GenericProvider([
                'clientId' => $config['client_id'],    // The client ID assigned to you by the provider
                'clientSecret' => $config['clientSecret'],   // The client password assigned to you by the provider
                'redirectUri' => $config['redirectUri'],
                'urlAuthorize' => $config['urlAuthorize'],
                'urlAccessToken' => $config['urlAccessToken'],
                'urlResourceOwnerDetails' => $config['urlResourceOwnerDetails']
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
