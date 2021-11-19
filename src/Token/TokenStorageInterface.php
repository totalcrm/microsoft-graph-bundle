<?php

namespace TotalCRM\MicrosoftGraph\Token;

use League\OAuth2\Client\Token\AccessToken;

interface TokenStorageInterface
{
    /**
     * @return AccessToken
     */
    public function getToken(): AccessToken;

    /**
     * @param AccessToken $token
     * @return mixed
     */
    public function setToken(AccessToken $token);
}
