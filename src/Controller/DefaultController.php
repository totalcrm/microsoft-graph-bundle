<?php

namespace TotalCRM\MicrosoftGraph\Controller;

use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphClient;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Exception;
use Twig\Error\RuntimeError;

/**
 * Class DefaultController
 * @package TotalCRM\MicrosoftGraph\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function requestAction(Request $request): RedirectResponse
    {
        /** @var MicrosoftGraphClient $client */
        $client = $this->get('microsoft_graph.client');

        return new RedirectResponse($client->redirect());
    }
    
    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function authAction(Request $request): RedirectResponse
    {
        /** @var MicrosoftGraphClient $client */
        $client = $this->get('microsoft_graph.client');
        $authorizationCode = $request->get('code');

        try {
            $client->setAuthorizationCode($authorizationCode);
        } catch (\Exception $e) {
        }

        try {
            $token = $client->refreshToken();
        } catch (\Exception $e) {
        }

        $redirectPage = $this->container->getParameter("microsoft_graph")["home_page"];

        return new RedirectResponse($this->generateUrl($redirectPage, $request->query->all()));
    }
}
