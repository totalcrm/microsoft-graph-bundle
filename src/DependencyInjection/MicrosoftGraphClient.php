<?php

namespace TotalCRM\MicrosoftGraph\DependencyInjection;

use TotalCRM\MicrosoftGraph\Exception\RedirectException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use RuntimeException;
use Exception;

/**
 * Class MicrosoftGraphClient
 * @package TotalCRM\MicrosoftGraph\DependencyInjection
 */
class MicrosoftGraphClient
{
    public const OAUTH2_SESSION_STATE_KEY = 'microsoft_graph_client_state';
    public const AUTHORITY_URL = 'https://login.microsoftonline.com/common';
    public const RESOURCE_ID = 'https://graph.microsoft.com';
    private AbstractProvider $provider;
    private RequestStack $requestStack;
    private bool $isStateless = false;
    private $config;
    private $router;
    private $storageManager;

    /**
     * MicrosoftGraphClient constructor.
     * @param RequestStack $requestStack
     * @param Container $container
     */
    public function __construct(RequestStack $requestStack, ContainerInterface $container)
    {
        $this->requestStack = $requestStack;
        $this->config = $container->getParameter('microsoft_graph');
        $this->storageManager = $container->get($this->config['storage_manager']);
        $this->router = $container->get('router');

        $options = [
            'clientId' => $this->config['client_id'],
            'clientSecret' => $this->config['client_secret'],
            'redirectUri' => "http://localhost:8000" . $container->get('router')->generate($this->config['redirect_uri']),
            'urlResourceOwnerDetails' => self::RESOURCE_ID . "/v1.0/me",
            "urlAccessToken" => self::AUTHORITY_URL . '/oauth2/v2.0/token',
            "urlAuthorize" => self::AUTHORITY_URL . '/oauth2/v2.0/authorize',

        ];
        $this->isStateless = $this->config['stateless'];
        $this->provider = new MicrosoftGraphProvider($options);
    }

    /**
     *  Return the configuration of Microsoft Graph
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return $this
     */
    public function setAsStateless(): self
    {
        $this->isStateless = true;
        return $this;
    }

    /**
     * Creates a RedirectResponse that will send the user to the OAuth2 server (e.g. send them to Facebook).
     * @return void
     * @throws RedirectException
     */
    public function redirect(): void
    {
        $options = [];
        $scopes = $this->config["scopes"];
        if (!empty($scopes)) {
            $options['scope'] = implode(" ", $scopes);
        }
        $url = $this->provider->getAuthorizationUrl($options);
        if (!$this->isStateless) {
            $this->getSession()->set(
                self::OAUTH2_SESSION_STATE_KEY,
                $this->provider->getState()
            );
        }

        throw  new RedirectException(new RedirectResponse($url));
    }

    /**
     * Call this after the user is redirected back to get the access token.
     * @return AccessToken
     * @throws Exception
     */
    public function getAccessToken(): AccessToken
    {
        if (!$this->isStateless) {
            $expectedState = $this->getSession()->get(self::OAUTH2_SESSION_STATE_KEY);
            $actualState = $this->getCurrentRequest()->query->get('state');
            if (!$actualState || ($actualState !== $expectedState)) {
                throw new RuntimeException('Invalid state');
            }
        }
        $code = $this->getCurrentRequest()->get('code');
        if (!$code) {
            throw new RuntimeException('No "code" parameter was found (usually this is a query parameter)!');
        }

        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        $this->storageManager->setToken($token);

        return $token;
    }

    /**
     * @return mixed
     */
    public function getstorageManager()
    {
        return $this->storageManager;
    }

    /**
     * @param AccessToken $accessToken
     * @return mixed
     */
    public function fetchUserFromToken(AccessToken $accessToken)
    {
        return $this->provider->getResourceOwner($accessToken);
    }

    /**
     * @return ResourceOwnerInterface
     * @throws Exception
     */
    public function fetchUser(): ResourceOwnerInterface
    {
        $token = $this->getAccessToken();

        return $this->fetchUserFromToken($token);
    }

    /**
     * Returns the underlying OAuth2 provider.
     * @return AbstractProvider
     */
    public function getOAuth2Provider(): AbstractProvider
    {
        return $this->provider;
    }

    /**
     * @return AccessToken
     * @throws Exception
     */
    public function getNewToken(): AccessToken
    {
        /** @var  $oldToken */
        $oldToken = $this->storageManager->getToken();

        if ($oldToken->hasExpired()) {
            if ($oldToken->getRefreshToken() === null) {
                throw new RuntimeException("No refresh Token");
            }
            $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $oldToken->getRefreshToken()
            ]);
            $this->storageManager->setToken($newAccessToken);

            return $newAccessToken;
        }

        return $oldToken;
    }

    /**
     * @return Request
     * @throws Exception
     */
    private function getCurrentRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            throw new RuntimeException('There is no "current request", and it is needed to perform this action');
        }

        return $request;
    }

    /**
     * @return null|SessionInterface
     * @throws Exception
     */
    private function getSession(): ?SessionInterface
    {
        $session = $this->getCurrentRequest()->getSession();
        if (!$session) {
            throw new RuntimeException('In order to use "state", you must have a session. Set the OAuth2Client to stateless to avoid state');
        }

        return $session;
    }


}