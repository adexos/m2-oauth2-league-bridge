<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Model;

class Config
{
    public const XML_PATH_BASE_URL = 'adexos/oauth2/base_url';
    public const XML_PATH_CLIENT_ID = 'adexos/oauth2/client_id';
    public const XML_PATH_CLIENT_SECRET = 'adexos/oauth2/client_secret';
    public const XML_PATH_AUTHORIZE_ENDPOINT = 'adexos/oauth2/authorize_endpoint';
    public const XML_PATH_ACCESS_TOKEN_ENDPOINT = 'adexos/oauth2/access_token_endpoint';
    public const XML_PATH_RESOURCE_OWNER_DETAILS_ENDPOINT = 'adexos/oauth2/resource_owner_details_endpoint';
}
