<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Persistence;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Framework\App\CacheInterface;

/**
 * Uses Magento native cache with specific node.
 */
class CachePersistence implements PersistenceInterface
{
    private const DEFAULT_ADX_OAUTH_BRIDGE_TAGS = ['adx_oauth2_league_bridge'];

    private CacheInterface $cache;

    /**
     * @var string[]
     */
    private array $tags;

    private int $specificLifetime;

    public function __construct(
        CacheInterface $cache,
        array $tags = self::DEFAULT_ADX_OAUTH_BRIDGE_TAGS,
        int $specificLifetime = 3600
    ) {
        $this->cache = $cache;
        $this->tags = $tags;
        $this->specificLifetime = $specificLifetime;
    }

    public function persist(AccessTokenInterface $accessToken, string $identifier): void
    {
        /* @noinspection JsonEncodingApiUsageInspection */
        $this->cache->save(
            json_encode($accessToken),
            $identifier,
            $this->tags,
            $this->specificLifetime
        );
    }

    public function retrieve(string $identifier): ?AccessTokenInterface
    {
        $accessToken = $this->cache->load($identifier);

        if (!$accessToken) {
            return null;
        }

        /* @noinspection JsonEncodingApiUsageInspection */
        return new AccessToken(json_decode($accessToken, true));
    }

    public function clear(string $identifier): void
    {
        $this->cache->remove($identifier);
    }
}
