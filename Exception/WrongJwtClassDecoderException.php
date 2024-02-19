<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Exception;

use Exception;

class WrongJwtClassDecoderException extends Exception
{
    public function __construct()
    {
        parent::__construct('Class provided must be a valid class and must implement `JwtResultInterface`');
    }
}
