<?php

namespace TotalCRM\MicrosoftGraph\DependencyInjection;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\token;

/**
 * Class MicrosoftGraphResourceOwner
 * @package TotalCRM\MicrosoftGraph\DependencyInjection
 */
class MicrosoftGraphResourceOwner implements ResourceOwnerInterface
{
    protected array $response;

    /**
     * MicrosoftGraphResourceOwner constructor.
     * @param array $response
     * @param $resourceOwnerId
     */
    public function __construct(array $response, $resourceOwnerId)
    {
        $this->response = $response;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->response['id'] ?: null;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['mail'] ?: null;
    }

    /**
     * @return string|null
     */
    public function getFirstname()
    {
        return $this->response['givenName'] ?: null;
    }

    /**
     * @return string|null
     */
    public function getLastname()
    {
        return $this->response['surname'] ?: null;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->response['name'] ?: null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}