<?php

namespace TotalCRM\MicrosoftGraph\DependencyInjection;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphResourceOwner as User;

/**
 * Class MicrosoftGraphProvider
 * @package TotalCRM\MicrosoftGraph\DependencyInjection
 */
class MicrosoftGraphProvider extends GenericProvider
{
    public const AUTHORITY_URL = 'https://login.microsoftonline.com/common';
    public const RESOURCE_ID = 'https://graph.microsoft.com';
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * MicrosoftGraphProvider constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * @param array $response
     * @param AccessToken $token
     * @return MicrosoftGraphResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): User
    {
        return new User($response, self::ACCESS_TOKEN_RESOURCE_OWNER_ID);
    }

}