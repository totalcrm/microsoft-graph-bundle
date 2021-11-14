<?php

namespace TotalCRM\MicrosoftGraph\DependencyInjection;

use DateTime;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Microsoft\Graph\Model\DateTimeTimeZone;

/**
 * Class MicrosoftGraphRequest
 * @package TotalCRM\MicrosoftGraph\DependencyInjection
 */
class MicrosoftGraphRequest
{
    private $client;
    private $graph;
    private $request;

    public function __construct(MicrosoftGraphClient $client)
    {
        $this->client = $client;
        $this->graph = new Graph();
    }

    public function setVersion($version = "")
    {
        if (in_array($version, ['v1.0', 'beta'])) {
            $this->graph->setApiVersion($version);
        } else {
            $version = $this->client->getConfig()['version'];
            if (in_array($version, ['v1.0', 'beta'])) {
                $this->graph->setApiVersion();
            }
        }
    }

    public function getToken()
    {
        return $this->client->getNewToken()->getToken();
    }

    public function setTokenGraph()
    {
        $this->graph->setAccessToken($this->getToken());
    }

    public function getConfig($key)
    {
        return $this->client->getConfig()[$key];
    }

    public function getPreferTimeZone()
    {
        return 'outlook.timezone="' . $this->getConfig('prefer_time_zone') . '"';
    }

    public function createRequest($requestType, $endpoint, $preferedTimeZone = False)
    {
        $this->setTokenGraph();
        $request = $this->graph->createRequest($requestType, $endpoint);
        if ($preferedTimeZone) {
            $request->addHeaders(["Prefer" => $this->getPreferTimeZone()]);
        }

        return $request;
    }

    public function createCollectionRequest($requestType, $endpoint, $preferedTimeZone = False)
    {
        $this->setTokenGraph();
        $createCollectionRequest = $this->graph->createCollectionRequest($requestType, $endpoint);
        if ($preferedTimeZone) {
            $createCollectionRequest->addHeaders(["Prefer" => $this->getPreferTimeZone()]);
        }

        return $createCollectionRequest;
    }

    /**
     * Format
     * @param DateTime $date
     * @return string
     */
    public function getDateMicrosoftFormat(DateTime $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }

    /**
     * @param DateTime $date
     * @return DateTimeTimeZone
     */
    public function getDateTimeTimeZone(DateTime $date): DateTimeTimeZone
    {
        $dateTime = $this->getDateMicrosoftFormat($date);
        $timezone = $this->getConfig('prefer_time_zone');

        return new DateTimeTimeZone(['dateTime' => $dateTime, 'timezone' => $timezone]);
    }
}