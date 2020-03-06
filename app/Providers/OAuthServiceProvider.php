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
            return new GenericProvider([
                'clientId' => '2',    // The client ID assigned to you by the provider
                'clientSecret' => 'project_pc_aaa',   // The client password assigned to you by the provider
                'redirectUri' => 'http://127.0.0.1:8000/callback',
                'urlAuthorize' => 'http://127.0.0.1:9501/authorize',
                'urlAccessToken' => 'http://127.0.0.1:9501/access_token',
                'urlResourceOwnerDetails' => 'http://127.0.0.1:9501/oauth2/lockdin/resource'
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
