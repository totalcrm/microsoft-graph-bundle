<?php

namespace TotalCRM\MicrosoftGraph\Entity;

use League\OAuth2\Client\Token\AccessToken;

/**
 * Interface MicrosoftGraphTokenInterface
 * @package TotalCRM\MicrosoftGraph\Entity
 */
interface MicrosoftGraphTokenInterface
{
    /**
     * @param AccessToken $accessToken
     * @return mixed
     */
    public function setMicrosoftGraphToken(AccessToken $accessToken);

    /**
     * @return mixed
     */
    public function getMicrosoftGraphToken();

}