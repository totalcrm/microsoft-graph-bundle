<?php

namespace TotalCRM\MicrosoftGraph\Manager;

use RuntimeException;
use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphRequest;

use Microsoft\Graph\Model;
use Microsoft\Graph\Exception\GraphException;
use GuzzleHttp\Exception\GuzzleException;
use Exception;
use DateTime;

/**
 * Class CalendarManager
 * @package TotalCRM\MicrosoftGraph\Manager
 */
class CalendarManager
{
    private MicrosoftGraphRequest $request;

    /**
     * CalendarManager constructor.
     * @param MicrosoftGraphRequest $request
     */
    public function __construct(MicrosoftGraphRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @return mixed
     * @throws Exception
     */
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

    /**
     * @param $eventId
     * @return mixed
     * @throws Exception
     */
    public function getEvent($eventId)
    {
        if ($eventId === null) {
            throw new RuntimeException("Your idEvent is null");
        }

        return $this->request
            ->createRequest('GET', '/me/events/' . $eventId, true)
            ->setReturnType(Model\Event::class)
            ->execute();
    }

    /**
     * Create an event
     * @param Model\Event|null $event
     * @return mixed|array|void
     * @throws Exception
     */
    public function addEvent(?Model\Event $event)
    {
        if (!$event instanceof Model\Event) {
            throw new RuntimeException("Your event is null");
        }

        return $this->request
            ->createRequest('POST', '/me/events', true)
            ->attachBody($event->jsonSerialize())
            ->setReturnType(Model\Event::class)
            ->execute();
    }

    /**
     * Update an event
     * @param Model\Event|null $event
     * @return mixed|array|void
     * @throws Exception
     */
    public function updateEvent(?Model\Event $event)
    {
        if (!$event instanceof Model\Event) {
            throw new RuntimeException("Your event is null");
        }

        return $this->request
            ->createRequest('PATCH', '/me/events/' . $event->getId(), true)
            ->attachBody($event->jsonSerialize())
            ->setReturnType(Model\Event::class)
            ->execute();
    }

    /**
     * Delete an event
     * @param $eventId
     * @return mixed|array
     * @throws Exception
     */
    public function deleteEvent($eventId)
    {
        if ($eventId === null) {
            throw new RuntimeException("Event id is null");
        }

        return $this->request
            ->createRequest('DELETE', '/me/events/' . $eventId, true)
            ->execute();
    }

    /**
     * Format
     * @param DateTime $date
     * @return string
     */
    public function formatDate(DateTime $date): string
    {
        return $this->request->getDateMicrosoftFormat($date);
    }

    /**
     *  Create a DateTimeTimeZone for Windows
     * With prefer time zone
     * @param DateTime $date
     * @return Model\DateTimeTimeZone
     */
    public function getDateTimeTimeZone(DateTime $date): Model\DateTimeTimeZone
    {
        return $this->request->getDateTimeTimeZone($date);
    }

}

