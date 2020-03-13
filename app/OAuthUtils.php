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

    /**
     * @var string
     */
    protected $uid;

    /**
     * 一个月
     */
    const ACCESS_TOKEN_CACHE_TTL = 2592000;

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

    public function clearAccessToken()
    {
        $uid = $this->getUid();
        if (empty($uid)) {
            $this->getLogger()->error(__METHOD__, ['e' => 'uid empty']);
            return;
        }

        $configKey = config('oauth.oauth.cache_access_token_key');
        $key = sprintf($configKey, $uid);
        if (!empty($this->accessToken)) {
            $this->accessToken = null;
        }

        try {
            $this->getCache()->delete($key);
        } catch (InvalidArgumentException $e) {
            $this->getLogger()->error(__METHOD__, ['e' => $e->getMessage()]);
            return;
        }

        session()->remove('user');
        session()->remove('uid');
    }

    /**
     * @return AccessTokenInterface|null
     */
    public function getAccessToken(): ?AccessTokenInterface
    {
        $uid = $this->getUid();
        if (empty($uid)) {
            return null;
        }

        $configKey = config('oauth.oauth.cache_access_token_key');
        $key = sprintf($configKey, $uid);
        if (empty($this->accessToken)) {
            try {
                $this->accessToken = $this->getCache()->get($key);
                if (empty($this->accessToken)) {
                    return null;
                }
            } catch (InvalidArgumentException $e) {
                $this->getLogger()->error(__METHOD__, ['e' => $e->getMessage()]);
                return null;
            }
        }

        if ($this->accessToken->hasExpired()) {
            try {
                $this->accessToken = $this->provider->getAccessToken(
                    'refresh_token',
                    ['refresh_token' => $this->accessToken->getRefreshToken()]
                );
                $this->getCache()->set($key, $this->accessToken, self::ACCESS_TOKEN_CACHE_TTL);
            } catch (IdentityProviderException $ex) {
                $this->getLogger()->error(__METHOD__, ['code' => $ex->getCode(), 'msg' => $ex->getMessage()]);
                return null;
            } catch (InvalidArgumentException $ex) {
                $this->getLogger()->error(__METHOD__, ['code' => $ex->getCode(), 'msg' => $ex->getMessage()]);
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
        return $this->cache ?? app('cache.store');
    }

    /**
     * @return string|null
     */
    public function getUid(): ?string
    {
        return $this->uid ?? session('uid');
    }

    /**
     * @param string $uid
     * @return OAuthUtils
     */
    public function setUid(string $uid): OAuthUtils
    {
        $this->uid = $uid;
        return $this;
    }
}
