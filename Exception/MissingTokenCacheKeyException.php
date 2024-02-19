<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Exception;

use Exception;

class MissingTokenCacheKeyException extends Exception
{
    public function __construct()
    {
        parent::__construct('Please provide the token cache key parameter');
    }
}
