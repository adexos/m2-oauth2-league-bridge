<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Test\Unit\Mock;

use Magento\Framework\Session\SessionManagerInterface;

class SessionManagerMock implements SessionManagerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    public function start()
    {
    }

    public function writeClose()
    {
    }

    public function isSessionExists()
    {
    }

    public function getSessionId()
    {
    }

    public function getName()
    {
    }

    public function setName($name)
    {
    }

    public function destroy(array $options = null)
    {
    }

    public function clearStorage()
    {
    }

    public function getCookieDomain()
    {
    }

    public function getCookiePath()
    {
    }

    public function getCookieLifetime()
    {
    }

    public function setSessionId($sessionId)
    {
    }

    public function regenerateId()
    {
    }

    public function expireSessionCookie()
    {
    }

    public function getSessionIdForHost($urlHost)
    {
    }

    public function isValidForHost($host)
    {
    }

    public function isValidForPath($path)
    {
    }

    public function setData(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }
}
