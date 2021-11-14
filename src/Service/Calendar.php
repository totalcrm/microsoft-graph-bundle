<?php

namespace TotalCRM\MicrosoftGraph\Service;

use DateTime;
use Microsoft\Graph\Model;
use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphRequest;

/**
 * Class Calendar
 * @package TotalCRM\MicrosoftGraph\Service
 */
class Calendar
{

    /**
     * Request prepared with Prefered time_zone
     * @var MicrosoftGraphRequest
     */
    private $request;

    /**
     * Calendar constructor.
     * @param MicrosoftGraphRequest $request
     */
    public function __construct(MicrosoftGraphRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param $idEvent
     * @return mixed
     */
    public function getEvent($idEvent)
    {
        if ($idEvent === null) {
            throw new Exception("Your idEvent is null");
        }

        return $this->request
            ->createRequest('GET', '/me/events/' . $idEvent, true)
            ->execute();
    }

    /**
     *  Create a DateTimeTimeZone for Windows
     * With prefer time zone
     * @param DateTime $date
     * @return Model\DateTimeTimeZone
     */
    public function getDateTimeTimeZone(DateTime $date)
    {
        return $this->request->getDateTimeTimeZone($date);
    }

    /**
     * Create an event
     * @param Model\Event $event
     * @return void
     */
    public function addEvent(Model\Event $event)
    {
        if ($event === null) {
            throw new Exception("Your event is null");
        }

        return $this->request
            ->createRequest('POST', '/me/events', true)
            ->attachBody($event->jsonSerialize())
            ->setReturnType(Model\Event::class)
            ->execute();
    }

    /**
     * Update an event
     * @param Model\Event $event
     * @return void
     */
    public function updateEvent(Model\Event $event)
    {
        if ($event === null) {
            throw new Exception("Your event is null");
        }

        return $this->request
            ->createRequest('PATCH', '/me/events/' . $event->getId(), true)
            ->attachBody($event->jsonSerialize())
            ->setReturnType(Model\Event::class)
            ->execute();
    }

    /**
     * Delete an event
     * @param $id
     * @return void
     */
    public function deleteEvent($id): void
    {
        if ($id === null) {
            throw new Exception(" id is null");
        }

        return $this->request
            ->createRequest('DELETE', '/me/events/' . $id, true)
            ->execute();
    }

    /**
     * Format
     * @param DateTime $date
     * @return string
     */
    public function formatDate(DateTime $date)
    {
        return $this->request->getDateMicrosoftFormat($date);
    }

    public function getEvents(DateTime $start, DateTime $end)
    {
        $start->setTime(0, 0, 0);
        $end->modify('+1 day');
        $startTime = $this->formatDate($start);
        $endTime = $this->formatDate($end);
        $route = "/me/calendarView?startDateTime=$startTime&endDateTime=$endTime";
        return $this->request->createCollectionRequest("GET", $route, true)
            ->setReturnType(Model\Event::class)
            ->execute();
    }

}

