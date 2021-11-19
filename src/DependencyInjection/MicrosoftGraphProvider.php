<?php

namespace TotalCRM\MicrosoftGraph\DependencyInjection;

use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphResourceOwner;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

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
     * @param array|null $options
     */
    public function __construct(?array $options = null)
    {
        if (!$options) {
            $options = [];
        }

        parent::__construct($options);
    }

    /**
     * @param array $response
     * @param AccessToken $token
     * @return MicrosoftGraphResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): MicrosoftGraphResourceOwner
    {
        return new MicrosoftGraphResourceOwner($response, self::ACCESS_TOKEN_RESOURCE_OWNER_ID);
    }

}