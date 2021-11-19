<?php

namespace TotalCRM\MicrosoftGraph\DependencyInjection;

use TotalCRM\MicrosoftGraph\Token\SessionStorage;
use TotalCRM\MicrosoftGraph\Exception\RedirectException;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    public const AUTHORITY_URL = 'https://login.microsoftonline.com/6b948520-d8da-4240-9a4a-cc6bc4ecb14c';
    public const RESOURCE_ID = 'https://graph.microsoft.com';
    private MicrosoftGraphProvider $provider;
    private ?OutputInterface $output;
    private array $config;
    //private $router;
    private $storageManager;

    private $expires;
    private string $cacheDirectory;

    /**
     * MicrosoftGraphClient constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->getParameter('microsoft_graph');
        $this->storageManager = $container->get($this->config['storage_manager']);
        //$this->router = $container->get('router');

        $this->expires = 1000 * 600 * 6;
        $this->cacheDirectory = $container->getParameter('kernel.project_dir') . '/cacheAdapter';
        
        $options = [
            'clientId' => $this->config['client_id'],
            'clientSecret' => $this->config['client_secret'],
            //'redirectUri' => "http://localhost:8000" . $container->get('router')->generate($this->config['redirect_uri']),
            'redirectUri' => "https://localhost/microsoft-graph/auth",
            'urlResourceOwnerDetails' => self::RESOURCE_ID . "/v1.0/me",
            "urlAccessToken" => self::AUTHORITY_URL . '/oauth2/v2.0/token',
            "urlAuthorize" => self::AUTHORITY_URL . '/oauth2/v2.0/authorize',
        ];
        
        $this->provider = new MicrosoftGraphProvider($options);
    }

    /**
     * @param $code
     */
    public function setAuthorizationCode($code): void
    {
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
        $this->storageManager->setToken($token);
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
     * Creates a RedirectResponse that will send the user to the OAuth2 server (e.g. send them to Facebook).
     * @param OutputInterface|null $output
     * @return void
     * @throws RedirectException
     */
    public function redirect(?OutputInterface $output = null): void
    {
        $options = [];
        $scopes = $this->config["scopes"];
        if (!empty($scopes)) {
            $options['scope'] = implode(" ", $scopes);
        }
        $url = $this->provider->getAuthorizationUrl($options);

        if ($output instanceof OutputInterface) {
            $output->writeln('<info>####################################################################</info>');
            $output->writeln('<info>Log in using the specified link</info>');
            $output->writeln('<info>'.$url.'</info>');
            $output->writeln('<info>####################################################################</info>');
            
            return;
        }

        throw new RedirectException(new RedirectResponse($url));
    }

    /**
     * Call this after the user is redirected back to get the access token.
     * @return AccessToken
     * @throws Exception
     */
    public function getAccessToken(): AccessToken
    {

        /** @var FilesystemAdapter $cache */
        $cache = new FilesystemAdapter('app.cache.authorization_code', $this->expires, $this->cacheDirectory);
        $cacheKey = 'authorization_code';
        $authorizationCode = null;

        $cacheItem = $cache->getItem($cacheKey);
        if ($cacheItem && $cacheItem->isHit()) {
            $authorizationCode = $cacheItem->get();
        }

        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $authorizationCode,
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
     * @return MicrosoftGraphProvider
     */
    public function getOAuth2Provider(): MicrosoftGraphProvider
    {
        return $this->provider;
    }

    /**
     * @return AccessToken
     * @throws Exception
     */
    public function getNewToken(): AccessToken
    {
        /** @var AccessToken $oldToken */
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

}