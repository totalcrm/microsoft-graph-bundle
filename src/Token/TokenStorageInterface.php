<?php


namespace TotalCRM\MicrosoftGraph\Token;

use League\OAuth2\Client\Token\AccessToken;

interface  TokenStorageInterface
{


    public function getToken();

    public function setToken(AccessToken $token);

}
