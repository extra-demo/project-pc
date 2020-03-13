<?php

namespace App\RestService;

use App\OAuthUtils;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

class PassportService
{
    /**
     * @var array
     */
    protected $data = [
        'headers' => [],
    ];

    /**
     * @var AccessToken
     */
    private $accessToken;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://127.0.0.1:9501'
        ]);
    }

    protected function withAccessToken(): self
    {
        if (empty($this->accessToken)) {
            throw new \RuntimeException("access token null");
        }

        if ($this->accessToken->hasExpired()) {
            $this->accessToken = OAuthUtils::getInstance()->getAccessToken();
        }

        $this->data['headers'] = array_merge($this->data['headers'], [
            'Authorization' => 'Bearer ' .$this->accessToken->getToken(),
        ]);

        return $this;
    }

    /**
     * @return array
     */
    public function getOauthUser(): array
    {
        $this->withAccessToken();
        $request = '/oauth/users';
        $response = $this->client->get($request,  $this->data);

        try {
            $data = $response->getBody()->getContents();
            $data = @json_decode($data, true);
            return $data;
        } catch (\Exception $exception) {
            //log info
            Log::error(__METHOD__, [$exception->getMessage()]);
            return [];
        }
    }

    /**
     * @param AccessTokenInterface $accessToken
     * @return PassportService
     */
    public function setAccessToken(AccessTokenInterface $accessToken): PassportService
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return AccessTokenInterface
     */
    public function getAccessToken(): AccessTokenInterface
    {
        return $this->accessToken;
    }
}
