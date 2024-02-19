<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Persistence;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\SessionManagerInterface;

class SessionPersistence implements PersistenceInterface
{
    private SessionManagerInterface $sessionManager;

    public function __construct(SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function persist(AccessTokenInterface $accessToken, string $identifier): void
    {
        /*
         * @noinspection PhpUndefinedMethodInspection
         * @see          SessionManager::__call()
         */
        $this->sessionManager->setData($identifier, $accessToken->jsonSerialize());
    }

    public function retrieve(string $identifier): ?AccessTokenInterface
    {
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $accessToken = $this->sessionManager->getData($identifier);

        if ($accessToken === null) {
            return null;
        }

        return new AccessToken($accessToken);
    }

    public function clear(string $identifier): void
    {
        /*
         * @noinspection PhpUndefinedMethodInspection
         * @see          SessionManager::__call()
         */
        $this->sessionManager->setData($identifier, null);
    }
}
