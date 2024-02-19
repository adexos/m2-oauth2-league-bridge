<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Persistence;

use League\OAuth2\Client\Token\AccessTokenInterface;

interface PersistenceInterface
{
    public function persist(AccessTokenInterface $accessToken, string $identifier): void;

    public function retrieve(string $identifier): ?AccessTokenInterface;

    public function clear(string $identifier): void;
}
