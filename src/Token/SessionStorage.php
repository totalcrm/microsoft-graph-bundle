<?php

namespace TotalCRM\MicrosoftGraph\Token;

use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SessionStorage
 * @package TotalCRM\MicrosoftGraph\Token
 */
class SessionStorage implements TokenStorageInterface
{
    private ContainerInterface $container;
    private Session $session;

    /**
     * SessionStorage constructor.
     * @param Session $session
     * @param ContainerInterface $container
     */
    public function __construct(Session $session, ContainerInterface $container)
    {
        $this->session = $session;
        $this->container = $container;
    }

    /**
     * @param AccessToken $token
     * @return void
     */
    public function setToken(AccessToken $token): void
    {
        $this->session->set('microsoft_graph_accesstoken', $token->getToken());
        $this->session->set('microsoft_graph_refreshtoken', $token->getRefreshToken());
        $this->session->set('microsoft_graph_expires', $token->getExpires());
        $this->session->set('microsoft_graph_resourceOwnerId', $token->getResourceOwnerId());
    }

    /**
     * @return AccessToken
     */
    public function getToken(): AccessToken
    {
        $options['access_token'] = $this->session->get('microsoft_graph_accesstoken');
        $options['refresh_token'] = $this->session->get('microsoft_graph_refreshtoken');
        $options['expires'] = $this->session->get('microsoft_graph_expires');
        $options['resource_owner_id'] = $this->session->get('microsoft_graph_resourceOwnerId');

        return new AccessToken($options);
    }
}
