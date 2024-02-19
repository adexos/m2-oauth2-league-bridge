<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Model;

class Time
{
    public function getNow(): int
    {
        return time();
    }
}
