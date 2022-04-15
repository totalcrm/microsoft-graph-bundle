<?php

namespace TotalCRM\MicrosoftGraph\Controller;

use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphClient;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class DefaultController
 * @package TotalCRM\MicrosoftGraph\Controller
 */
class DefaultController extends AbstractController
{
    protected ContainerInterface $containerInterface;
    protected MicrosoftGraphClient $client;

    /**
     * DefaultController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->containerInterface = $container;
        $this->client = $this->containerInterface->get('microsoft_graph.client');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function requestAction(Request $request): RedirectResponse
    {
        return new RedirectResponse($this->client->redirect());
    }
    
    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function authAction(Request $request): RedirectResponse
    {
        $authorizationCode = $request->get('code');

        try {
            $this->client->setAuthorizationCode($authorizationCode);
        } catch (\Exception $e) {
        }

        try {
            $token = $this->client->refreshToken();
        } catch (\Exception $e) {
        }

        $redirectPage = $this->containerInterface->getParameter("microsoft_graph")["home_page"];

        return new RedirectResponse($this->generateUrl($redirectPage, $request->query->all()));
    }
}
