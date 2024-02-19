<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Test\Unit\Persistence;

use Adexos\Oauth2LeagueBridge\Persistence\SessionPersistence;
use Adexos\Oauth2LeagueBridge\Test\Unit\Mock\SessionManagerMock;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;

class SessionPersistenceTest extends TestCase
{
    private const RANDOM_IDENTIFIER_KEY = 's78dm';

    private ?SessionPersistence $sessionPersistence = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionPersistence = new SessionPersistence(new SessionManagerMock());
    }

    public function testDataIsPersistedInSession(): void
    {
        $accessToken = new AccessToken(
            [
                'access_token'  => 'a_new_access_token',
                'refresh_token' => 'my_refresh_token',
                'expires'       => 1688375176
            ]
        );

        $this->sessionPersistence->persist($accessToken, self::RANDOM_IDENTIFIER_KEY);

        $accessTokenFetched = $this->sessionPersistence->retrieve(self::RANDOM_IDENTIFIER_KEY);

        self::assertEquals('a_new_access_token', $accessTokenFetched->getToken());
        self::assertEquals('my_refresh_token', $accessTokenFetched->getRefreshToken());
        self::assertEquals(1688375176, $accessTokenFetched->getExpires());
    }

    public function testDataRetrievedWhenNull(): void
    {
        self::assertNull($this->sessionPersistence->retrieve(self::RANDOM_IDENTIFIER_KEY));
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

        $this->sessionPersistence->persist($accessToken, self::RANDOM_IDENTIFIER_KEY);

        $accessTokenFetched = $this->sessionPersistence->retrieve(self::RANDOM_IDENTIFIER_KEY);

        self::assertEquals('a_new_access_token', $accessTokenFetched->getToken());
        self::assertEquals('my_refresh_token', $accessTokenFetched->getRefreshToken());
        self::assertEquals(1688375176, $accessTokenFetched->getExpires());

        $this->sessionPersistence->clear(self::RANDOM_IDENTIFIER_KEY);

        self::assertNull($this->sessionPersistence->retrieve(self::RANDOM_IDENTIFIER_KEY));
    }
}
