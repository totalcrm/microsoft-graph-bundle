<?php

namespace TotalCRM\MicrosoftGraph\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphClient;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use DateTime;
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
     * @return mixed
     * @throws Exception
     */
    public function indexAction(Request $request)
    {
        $client = $this->container->get('microsoft_graph.client');
        $session = $this->container->get('session');

        /*
        try {
            $client->getNewToken();
        } catch (Exception $ex) {
            $client->redirect();
        }

        $startTime = new DateTime("01-05-2017");
        $endTime = new DateTime("29-05-2017");
        $calendar = $this->get('microsoft_graph.calendar');
        $events = $calendar->getEvents($startTime, $endTime);
        $event = $calendar->getEvent($events[0]->getId());

        $newEvent = new Model\Event();

        $start = $calendar->getDateTimeTimeZone(new DateTime('Now next minute'));
        $end = $calendar->getDateTimeTimeZone(new DateTime('Now next hour'));
        $newEvent->setSubject('Controller Test Token');
        $newEvent->setStart($start);
        $newEvent->setEnd($end);

        $event = $calendar->addEvent($newEvent);

        $updateEvent = new Model\Event();
        $updateEvent->setId($event->getId());
        $updateEvent->setSubject('Controller Test Token updated');
        $event = $calendar->updateEvent($updateEvent);
        $response = $calendar->deleteEvent($updateEvent->getID());

        $session->set('microsoft_graph_expires', time() - 51);
        return $this->render('TotalCRMMicrosoftGraph:Default:index.html.twig');
        */
    }

    public function connectAction()
    {
        return $this->get('microsoft_graph.client')->redirect();
    }

    /**
     * After going to Office365, you're redirected back here
     * because this is the "graph_check" you configured
     * in config.yml
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function connectCheckAction(Request $request): RedirectResponse
    {
        /** @var MicrosoftGraphClient $client */
        $client = $this->get('microsoft_graph.client');

        $authorizationCode = $request->get('code');
        $client->setAuthorizationCode($authorizationCode);

        try {
            $token = $client->getNewToken();
        } catch (\Exception $exception) {
            return new RedirectResponse($client->redirect());
        }

        $homePage = $this->container->getParameter("microsoft_graph")["home_page"];

        return new RedirectResponse($this->generateUrl($homePage));
    }
}
