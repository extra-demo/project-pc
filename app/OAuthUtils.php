<?php


namespace App;


use Illuminate\Support\Facades\Cache;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class OAuthUtils
{
    protected static $instance;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CacheInterface
     */
    protected $cache;

    protected $config;

    /**
     * @var GenericProvider
     */
    protected $provider;

    private function __construct()
    {
        $this->provider = app(GenericProvider::class);
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (empty(static::$instance)) {
            static::$instance = new OAuthUtils();
        }

        return static::$instance;
    }

    /**
     * @param AccessTokenInterface $accessToken
     * @return OAuthUtils
     */
    public function setAccessToken(AccessTokenInterface $accessToken): OAuthUtils
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return AccessTokenInterface|null
     */
    public function getAccessToken(): ?AccessTokenInterface
    {
        $configKey = config('oauth.oauth.cache_access_token_key');
        $uid = session()->get('uid');
        $key = sprintf($configKey, $uid);
        if (empty($this->accessToken)) {
            try {
                $this->accessToken = $this->cache->get($key);
            } catch (InvalidArgumentException $e) {
                $this->logger->error(__METHOD__, ['e' => $e->getMessage()]);
                return null;
            }
        }

        if ($this->accessToken->hasExpired()) {
            try {
                $this->accessToken = $this->provider->getAccessToken(
                    'refresh_token',
                    ['refresh_token' => $this->accessToken->getRefreshToken()]
                );
                $this->cache->set($key, $this->accessToken, $this->accessToken->getExpires());
            } catch (IdentityProviderException $ex) {
                $this->logger->error(__METHOD__, ['code' => $ex->getCode(), 'msg' => $ex->getMessage()]);
                return null;
            } catch (InvalidArgumentException $ex) {
                $this->logger->error(__METHOD__, ['code' => $ex->getCode(), 'msg' => $ex->getMessage()]);
                return null;
            }
        }

        return $this->accessToken;
    }

    /**
     * @param LoggerInterface $logger
     * @return OAuthUtils
     */
    public function setLogger(LoggerInterface $logger): OAuthUtils
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger ?? new NullLogger();
    }

    /**
     * @param CacheInterface $cache
     * @return OAuthUtils
     */
    public function setCache(CacheInterface $cache): OAuthUtils
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return $this->cache ?? app(Cache::class);
    }
}
