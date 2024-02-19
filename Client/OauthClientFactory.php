<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Client;

use Adexos\Oauth2LeagueBridge\Model\Time;
use Adexos\Oauth2LeagueBridge\Persistence\PersistenceInterface;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\OptionProvider\OptionProviderInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericProviderFactory;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Provides a layer that allow to do specific behavior on endpoints along with
 * instantiating Oauth client with all Magento configuration.
 */
abstract class OauthClientFactory
{
    private ?GenericProvider $genericProvider = null;

    protected GenericProviderFactory $genericProviderFactory;

    protected ScopeConfigInterface $scopeConfig;

    protected EncryptorInterface $encryptor;

    protected Time $time;

    protected string $baseUrlConfigPath;

    protected string $clientIdConfigPath;

    protected string $clientSecretConfigPath;

    protected string $authorizeEndpointConfigPath;

    protected string $accessTokenEndpointConfigPath;

    protected string $resourceOwnerDetailsEndpointConfigPath;

    private ?PersistenceInterface $persistence;

    private ?OptionProviderInterface $optionProvider;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        GenericProviderFactory $genericProviderFactory,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Time $time,
        string $baseUrlConfigPath,
        string $clientIdConfigPath,
        string $clientSecretConfigPath,
        string $authorizeEndpointConfigPath,
        string $accessTokenEndpointConfigPath,
        string $resourceOwnerDetailsEndpointConfigPath,
        PersistenceInterface $persistence = null,
        OptionProviderInterface $optionProvider = null
    ) {
        $this->genericProviderFactory = $genericProviderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->time = $time;
        $this->baseUrlConfigPath = $baseUrlConfigPath;
        $this->clientIdConfigPath = $clientIdConfigPath;
        $this->clientSecretConfigPath = $clientSecretConfigPath;
        $this->authorizeEndpointConfigPath = $authorizeEndpointConfigPath;
        $this->accessTokenEndpointConfigPath = $accessTokenEndpointConfigPath;
        $this->resourceOwnerDetailsEndpointConfigPath = $resourceOwnerDetailsEndpointConfigPath;
        $this->persistence = $persistence;
        $this->optionProvider = $optionProvider;
    }

    /**
     * @throws IdentityProviderException
     */
    public function getCurrentTokenWithRefresh(string $cacheIdentifier): ?AccessTokenInterface
    {
        if ($this->persistence === null) {
            return null;
        }

        $accessToken = $this->persistence->retrieve($cacheIdentifier);

        // set a 5 minutes delay to refresh the token and not use an almost expired one
        if ($accessToken && ($accessToken->getExpires() - (5 * 60)) < $this->time->getNow()) {
            // Master token types do not have refresh token
            if ($accessToken->getRefreshToken() === null) {
                return null;
            }

            $grantType = new RefreshToken();
            $options = ['refresh_token' => $accessToken->getRefreshToken()];

            return $this->getAccessTokenWithPersistence($grantType, $cacheIdentifier, $options);
        }

        return $accessToken;
    }

    /**
     * @throws IdentityProviderException
     */
    public function getAccessTokenWithPersistence(
        $grantType,
        string $identifier,
        array $options = []
    ): AccessTokenInterface {
        $accessToken = $this->getGenericProvider()->getAccessToken($grantType, $options);

        if ($this->persistence !== null) {
            $this->persistence->persist($accessToken, $identifier);
        }

        return $accessToken;
    }

    public function getGenericProvider(): GenericProvider
    {
        if ($this->genericProvider === null) {
            $this->genericProvider = $this->genericProviderFactory->create([
                'options' => [
                    'clientId' => $this->encryptor->decrypt(
                        $this->scopeConfig->getValue(
                            $this->clientIdConfigPath
                        )
                    ),
                    'clientSecret' => $this->encryptor->decrypt(
                        $this->scopeConfig->getValue(
                            $this->clientSecretConfigPath
                        )
                    ),
                    'urlAuthorize'            => $this->getFullUrl($this->authorizeEndpointConfigPath),
                    'urlAccessToken'          => $this->getFullUrl($this->accessTokenEndpointConfigPath),
                    'urlResourceOwnerDetails' => $this->getFullUrl($this->resourceOwnerDetailsEndpointConfigPath)
                ],
                'collaborators' => [
                    'optionProvider' => $this->optionProvider
                ]
            ]);
        }

        return $this->genericProvider;
    }

    protected function getFullUrl(?string $endpoint): string
    {
        if ($endpoint === '') {
            return '';
        }

        return sprintf('%s%s', $this->getBaseUrl(), $this->scopeConfig->getValue($endpoint));
    }

    /**
     * Remove trailing slash if it exists to have always a good URL.
     */
    private function getBaseUrl(): string
    {
        $baseUrl = $this->scopeConfig->getValue($this->baseUrlConfigPath);

        $endsSlash = substr($baseUrl, -1) === '/';

        if ($endsSlash === true) {
            $baseUrl = substr($baseUrl, 0, -1);
        }

        return $baseUrl;
    }

    public function clear(string $identifier): void
    {
        $this->persistence->clear($identifier);
    }
}
