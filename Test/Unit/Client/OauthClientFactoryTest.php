<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Test\Unit\Client;

use Adexos\Oauth2LeagueBridge\Client\OauthClientFactory;
use Adexos\Oauth2LeagueBridge\Model\Time;
use Adexos\Oauth2LeagueBridge\Persistence\PersistenceInterface;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericProviderFactory;
use League\OAuth2\Client\Token\AccessToken;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OauthClientFactoryTest extends TestCase
{
    private ?OauthClientFactory $oauthClientFactory = null;

    public function testNotExpiredTokenWillBeRetrieved(): void
    {
        $this->createOauthClientFactory(
            $this->createTime(1652688300),
            $this->createPersistence(1652688999)
        );

        $accessToken = $this->oauthClientFactory->getCurrentTokenWithRefresh('');

        self::assertNotNull($accessToken);
    }

    public function testExpiredTokenWillNotBeRetrieved(): void
    {
        $this->createOauthClientFactory(
            $this->createTime(1652688300),
            $this->createPersistence(1652601901)
        );

        $accessToken = $this->oauthClientFactory->getCurrentTokenWithRefresh('');

        self::assertNull($accessToken);
    }

    public function testExpiredTokenIn5minutesWillNotBeRetrieved(): void
    {
        $this->createOauthClientFactory(
            $this->createTime(1652688000),
            $this->createPersistence(1652688299)
        );

        $accessToken = $this->oauthClientFactory->getCurrentTokenWithRefresh('');

        self::assertNull($accessToken);
    }

    private function createOauthClientFactory(Time $time, PersistenceInterface $persistence): void
    {
        $genericProvider = $this->getMockBuilder(GenericProviderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $genericProvider->method('create')
            ->willReturn($this->getMockBuilder(GenericProvider::class)->disableOriginalConstructor()->getMock());

        $this->oauthClientFactory = new class(
            $genericProvider,
            $this->createMock(ScopeConfigInterface::class),
            $time,
            '',
            '',
            '',
            '',
            '',
            '',
            $persistence
        ) extends OauthClientFactory {
        };
    }

    private function createPersistence(int $expires): PersistenceInterface
    {
        $persistence = $this->createMock(PersistenceInterface::class);
        $persistence->method('retrieve')->willReturn(
            new AccessToken([
                'access_token' => 'blabla',
                'expires'      => $expires
            ])
        );

        return $persistence;
    }

    private function createTime(int $timestamp): Time
    {
        $time = $this->createMock(Time::class);
        $time->method('getNow')->willReturn($timestamp);

        return $time;
    }
}
