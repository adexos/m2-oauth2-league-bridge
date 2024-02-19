<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Test\Integration\Persistence;

use Adexos\Oauth2LeagueBridge\Persistence\CachePersistence;
use League\OAuth2\Client\Token\AccessToken;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CachePersistenceTest extends TestCase
{
    private const RANDOM_CACHE_KEY = 'ExrJp8Cwq3';

    private ?CachePersistence $cachePersistence = null;

    protected function setUp(): void
    {
        $this->cachePersistence = ObjectManager::getInstance()->get(CachePersistence::class);

        // cleanup everything before running a test
        $this->cachePersistence->clear(self::RANDOM_CACHE_KEY);
    }

    public function testDataIsPersistedInRedis(): void
    {
        $accessToken = new AccessToken(
            [
                'access_token'  => 'a_new_access_token',
                'refresh_token' => 'my_refresh_token',
                'expires'       => 1688375176
            ]
        );

        $this->cachePersistence->persist($accessToken, self::RANDOM_CACHE_KEY);

        $accessTokenFetched = $this->cachePersistence->retrieve(self::RANDOM_CACHE_KEY);

        self::assertEquals('a_new_access_token', $accessTokenFetched->getToken());
        self::assertEquals('my_refresh_token', $accessTokenFetched->getRefreshToken());
        self::assertEquals(1688375176, $accessTokenFetched->getExpires());
    }

    public function testDataRetrievedWhenNull(): void
    {
        self::assertNull($this->cachePersistence->retrieve(self::RANDOM_CACHE_KEY));
    }

    public function testDataIsCorrectlyCleared(): void
    {
        $accessToken = new AccessToken(
            [
                'access_token'  => 'a_new_access_token',
                'refresh_token' => 'my_refresh_token',
                'expires'       => 1688375176
            ]
        );

        $this->cachePersistence->persist($accessToken, self::RANDOM_CACHE_KEY);

        $accessTokenFetched = $this->cachePersistence->retrieve(self::RANDOM_CACHE_KEY);

        self::assertEquals('a_new_access_token', $accessTokenFetched->getToken());
        self::assertEquals('my_refresh_token', $accessTokenFetched->getRefreshToken());
        self::assertEquals(1688375176, $accessTokenFetched->getExpires());

        $this->cachePersistence->clear(self::RANDOM_CACHE_KEY);

        self::assertNull($this->cachePersistence->retrieve(self::RANDOM_CACHE_KEY));
    }
}
