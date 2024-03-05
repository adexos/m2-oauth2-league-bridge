# Adexos_Oauth2LeagueBridge

This module is intended to be a bridge client connection from OAuth 2 implementation for Magento 2.

## Installation

You can install it by typing : `composer require adexos/m2-oauth2-league-bridge`

## How to use ?

Create your own client extending the bridge one :

```php
<?php

declare(strict_types=1);

namespace Adexos\MyIdentityOauthModule\Client;

use Adexos\Oauth2LeagueBridge\Client\OauthClientFactory;

class MyOauthIdentity extends OauthClientFactory
{

}
```

This class can be empty, it should only be created for the purpose of the `di.xml`.

Then you can add your own configuration in the `di.xml` :

```xml
<type name="Adexos\MyIdentityOauthModule\Client\MyOauthIdentity">
    <arguments>
        <argument name="baseUrlConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_BASE_URL
        </argument>
        <argument name="clientIdConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_CLIENT_ID
        </argument>
        <argument name="clientSecretConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_CLIENT_SECRET
        </argument>
        <argument name="authorizeEndpointConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_AUTHORIZE_ENDPOINT
        </argument>
        <argument name="accessTokenEndpointConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_ACCESS_TOKEN_ENDPOINT
        </argument>
        <argument name="resourceOwnerDetailsEndpointConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_RESOURCE_OWNER_DETAILS_ENDPOINT_CONFIG_PATH
        </argument>
        <argument name="persistence" xsi:type="object">
            Adexos\Oauth2LeagueBridge\Persistence\SessionPersistence
        </argument>
    </arguments>
</type>
```

The paths refer to your configuration. It's up to you to create your own `system.xml` depending on the path you give.

**Optional** : You can set the backend_model entry in your `system.xml` as "Magento\Config\Model\Config\Backend\Encrypted" if you want to store your client secret in an encrypted way.

Here is a template as an example :

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Config:etc/system_file.xsd">
    <system>
        <tab id="adexos_tab" translate="label" sortOrder="145">
            <label>Adexos</label>
        </tab>

        <section id="adx" translate="label" sortOrder="300" showInDefault="1" showInWebsite="1" showInStore="1">
            <class>separator-top</class>
            <label>Reach 5</label>
            <tab>adeo_tab</tab>
            <resource>Magento_Config::config</resource>
            <group id="oauth2_identity" translate="label" type="text" sortOrder="0" showInDefault="1" showInWebsite="1"
                   showInStore="1">
                <label>[ADX Identity] Configuration</label>
                <field id="is_enabled" translate="label" type="select" sortOrder="1" showInDefault="1" showInWebsite="1"
                       showInStore="1">
                    <label>Is enabled</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                <field id="base_url" translate="label" type="text" sortOrder="20" showInDefault="1" showInWebsite="1"
                       showInStore="1" canRestore="1">
                    <label>Base Endpoint URL</label>
                </field>
                <field id="client_id" translate="label" type="obscure" sortOrder="30" showInDefault="1"
                       showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Client ID</label>
                    <backend_model>Magento\Config\Model\Config\Backend\Encrypted</backend_model>
                </field>
                <field id="client_secret" translate="label" type="obscure" sortOrder="40" showInDefault="1"
                       showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Client Secret</label>
                    <backend_model>Magento\Config\Model\Config\Backend\Encrypted</backend_model>
                </field>
                <field id="public_key_jwt_token" translate="label" type="textarea" sortOrder="50" showInDefault="1"
                       showInWebsite="1" showInStore="1" canRestore="1">
                    <label>RSA Public Key to decode JWT Token properly and ensure security</label>
                </field>
                <field id="authorize_endpoint" translate="label" type="text" sortOrder="60" showInDefault="1"
                       showInWebsite="1"
                       showInStore="1" canRestore="1">
                    <label>Authorize Endpoint</label>
                </field>
                <field id="access_token_endpoint" translate="label" type="text" sortOrder="70" showInDefault="1"
                       showInWebsite="1"
                       showInStore="1" canRestore="1">
                    <label>Access Token Endpoint</label>
                </field>
            </group>
        </section>
    </system>
</config>
```

Please do not forget to create a `config.xml` as well.

Finally, you can use it this way :

```php
<?php

declare(strict_types=1);

namespace Adexos\MyIdentityOauthModule\Http\Token;

use Adexos\MyIdentityOauthModule\Client\MyOauthIdentity;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Magento\Framework\Exception\LocalizedException;

class IdentityTokenStrategy
{
    private MyOauthIdentity $myOauthIdentity;

    public function __construct(MyOauthIdentity $myOauthIdentity)
    {
        $this->myOauthIdentity = $myOauthIdentity;
    }

    /**
     * @throws LocalizedException
     * @throws IdentityProviderException
     */
    public function find(): ?string
    {
        $accessToken = $this->myOauthIdentity->getAccessTokenWithPersistence(
              new Password(),
              'custom_identifier'
              ['username' => 'a', 'password' => 'b', 'scope' => 'custom_scopes']
        );

        if ($accessToken === null) {
            return null;
        }

        return $accessToken->getToken();
    }
}
```

Of course you need to update the `grant_type` and the options depending on your needs.
The identifier set as a second parameter will be used to persist the token somewhere based on your configuration.

If you now want to retrieve your token and update it with the refresh token if it exists, you can simply call :

```php
$accessToken = $this->myOauthIdentity->getCurrentTokenWithRefresh('custom_identifier');
```

## Cache systems

There is two major cache systems used in this module to store your OAuth tokens :

- Session cache
- Magento native cache that drives file, Redis and database cache

### Session cache

This cache is especially useful to use with Identity tokens (login) that must be stored within the customer session
data, here is how you can implement it :

```xml
<type name="Adexos\MyIdentityOauthModule\Client\MyOauthIdentity">
    <arguments>
        <argument name="baseUrlConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_BASE_URL
        </argument>
        <argument name="clientIdConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_CLIENT_ID
        </argument>
        <argument name="clientSecretConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_CLIENT_SECRET
        </argument>
        <argument name="authorizeEndpointConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_AUTHORIZE_ENDPOINT
        </argument>
        <argument name="accessTokenEndpointConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_ACCESS_TOKEN_ENDPOINT
        </argument>
        <argument name="resourceOwnerDetailsEndpointConfigPath" xsi:type="string">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_RESOURCE_OWNER_DETAILS_ENDPOINT_CONFIG_PATH
        </argument>
        <argument name="persistence" xsi:type="object">
            Adexos\Oauth2LeagueBridge\Persistence\SessionPersistence
        </argument>
    </arguments>
</type>
```

### Classic cache

It uses Magento native system, so you can use any cache that Magento supports : file, Redis or database.

This cache is more useful when you don't need to store the token for the current session but in a more global context
such as a Management token.

Here is how you can implement it :

```xml
<type name="Adexos\MyManagementOauthModule\Client\MyOauthManagement">
    <arguments>
        <argument name="baseUrlConfigPath" xsi:type="const">
            Adexos\MyManagementOauthModule\Model\Config::XML_PATH_BASE_URL
        </argument>
        <argument name="clientIdConfigPath" xsi:type="const">
            Adexos\MyManagementOauthModule\Model\Config::XML_PATH_CLIENT_ID
        </argument>
        <argument name="clientSecretConfigPath" xsi:type="const">
            Adexos\MyManagementOauthModule\Model\Config::XML_PATH_CLIENT_SECRET
        </argument>
        <argument name="authorizeEndpointConfigPath" xsi:type="const">
            Adexos\MyManagementOauthModule\Model\Config::XML_PATH_AUTHORIZE_ENDPOINT
        </argument>
        <argument name="accessTokenEndpointConfigPath" xsi:type="const">
            Adexos\MyManagementOauthModule\Model\Config::XML_PATH_ACCESS_TOKEN_ENDPOINT
        </argument>
        <argument name="resourceOwnerDetailsEndpointConfigPath" xsi:type="string">
            Adexos\MyManagementOauthModule\Model\Config::XML_PATH_RESOURCE_OWNER_DETAILS_ENDPOINT_CONFIG_PATH
        </argument>
        <argument name="persistence" xsi:type="object">AdexosManagementOauthPersistence</argument>
    </arguments>
</type>

<virtualType name="AdexosManagementOauthPersistence" type="Adexos\Oauth2LeagueBridge\Persistence\CachePersistence">
    <arguments>
        <argument name="cache" xsi:type="object">AdexosManagementOauthDataCache</argument>
    </arguments>
</virtualType>

<virtualType name="AdexosManagementOauthDataCache" type="Magento\Framework\App\Cache">
    <arguments>
        <argument name="cacheIdentifier" xsi:type="string">adexos_management_oauth_data_cache</argument>
    </arguments>
</virtualType>
```

Please note that the `cacheIdentifier` will be used in the `env.php` file to determine how you are storing your tokens :

#### Database

`env.php`

```php
    'cache' => [
        'frontend' => [
            'adexos_management_oauth_data_cache' => [
                'backend' => \Magento\Framework\Cache\Backend\Database::class,
                'backend_options' => []
            ]
        ]
    ]
```

##### Redis

`env.php`

```php
'cache' => [
        'frontend' => [
            'adexos_management_oauth_data_cache' => [
                'backend' => \Magento\Framework\Cache\Backend\Redis::class,
                'backend_options' => [
                    'server' => 'redis',
                    'database' => '3',
                    'port' => '6379',
                    'compress_data' => '1'
                ]
            ]
        ]
    ]
```

/!\ Please use a specific database to the cache, do not use the same database as the session.

#### File

`env.php`

```php
    'cache' => [
        'frontend' => [
            'adexos_management_oauth_data_cache' => [
                'backend' => 'database',
                'backend_options' => []
            ]
        ]
    ]
```

### Cache lifetime

If you need to add a specific lifetime for the cache **which is 1 hour by default**, you can override in your `di.xml`
the `specificLifetime` property :

```xml
<virtualType name="AdexosManagementOauthPersistence" type="Adexos\Oauth2LeagueBridge\Persistence\CachePersistence">
    <arguments>
        <argument name="cache" xsi:type="object">AdexosManagementOauthDataCache</argument>
        <!-- 2 days -->
        <argument name="specificLifetime" xsi:type="number">172800</argument>
    </arguments>
</virtualType>
```

### Tags

Cache is regrouped by tags. You may want to specify tags to clean specific global area of the cache you created.

In the default configuration, the tag is : `adx_oauth2_league_bridge`

You can override it by updating your `di.xml` through the property `tags` :

```xml

<virtualType name="AdexosManagementOauthPersistence" type="Adexos\Oauth2LeagueBridge\Persistence\CachePersistence">
    <arguments>
        <argument name="cache" xsi:type="object">AdexosManagementOauthDataCache</argument>
        <!-- 2 days -->
        <argument name="specificLifetime" xsi:type="number">172800</argument>
        <argument name="tags" xsi:type="array">
            <item name="fstTag" xsi:type="string">my_first_tag</item>
            <item name="sndTag" xsi:type="string">my_second_tag</item>
        </argument>
    </arguments>
</virtualType>
```

You can add many tags you want.

## Decoding the token

You can decode the token after you fetched it, it will also do the verification with the public key to ensure the
integrity of the received token :

```php
$jwtResponse = $this->client->getAccessTokenWithPersistence(
    new Password(),
    IdentityTokenStrategy::OAUTH_TOKEN_CACHE_IDENTIFIER,
    ['username' => $username, 'password' => $password, 'scope' => self::TOKEN_SCOPE]
);

$this->jwtDecoder->decode(
    $token->getValues()['id_token'],
    IdTokenDTO::class,
    $this->scopeConfig->getValue(Config::XML_PATH_PUBLIC_KEY_JWT_TOKEN)
);
```

The `jwtDecoder` class is `Adexos\Oauth2LeagueBridge\Decoder\JwtDecoder`

As a second parameter of the `decode` method, you must pass a DTO
implementing `Adexos\Oauth2LeagueBridge\Decoder\Model\JwtResultInterface` which is the content representation of the
token. After that, you can manipulate the token result through an object and do whatever you want.


## Alternative option provider (HttpBasicAuth/PostAuth)

The default option provider for the credentials concerning the access token is the `PostAuthOptionProvider`.

However, there are some systems when you need to instead use a `HttpBasicAuthOptionProvider` or even a custom one.

You simply need to add it in the `di.xml` of your client : 

```xml
<type name="Adexos\MyIdentityOauthModule\Client\MyOauthIdentity">
    <arguments>
        <argument name="baseUrlConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_BASE_URL
        </argument>
        <argument name="clientIdConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_CLIENT_ID
        </argument>
        <argument name="clientSecretConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_CLIENT_SECRET
        </argument>
        <argument name="authorizeEndpointConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_AUTHORIZE_ENDPOINT
        </argument>
        <argument name="accessTokenEndpointConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_ACCESS_TOKEN_ENDPOINT
        </argument>
        <argument name="resourceOwnerDetailsEndpointConfigPath" xsi:type="const">
            Adexos\MyIdentityOauthModule\Model\Config::XML_PATH_RESOURCE_OWNER_DETAILS_ENDPOINT_CONFIG_PATH
        </argument>
        <argument name="persistence" xsi:type="object">
            Adexos\Oauth2LeagueBridge\Persistence\SessionPersistence
        </argument>
        <argument name="optionProvider" xsi:type="object">
            League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider
        </argument>
    </arguments>
</type>
```

If you need to implement your own auth provider, simply create a class implementing `League\OAuth2\Client\OptionProvider\OptionProviderInterface`.
