<?php

namespace TotalCRM\MicrosoftGraph\DependencyInjection;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Microsoft\Graph\Model\DateTimeTimeZone;
use DateTime;

/**
 * Class MicrosoftGraphRequest
 * @package TotalCRM\MicrosoftGraph\DependencyInjection
 */
class MicrosoftGraphRequest
{
    private $client;
    private $graph;
    private $request;

    /**
     * MicrosoftGraphRequest constructor.
     * @param MicrosoftGraphClient $client
     */
    public function __construct(MicrosoftGraphClient $client)
    {
        $this->client = $client;
        $this->graph = new Graph();
    }

    /**
     * @param string $version
     */
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

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getToken()
    {
        return $this->client->getNewToken()->getToken();
    }

    /**
     * @throws \Exception
     */
    public function setTokenGraph()
    {
        $this->graph->setAccessToken($this->getToken());
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->client->getConfig()[$key];
    }

    /**
     * @return string
     */
    public function getPreferTimeZone()
    {
        return 'outlook.timezone="' . $this->getConfig('prefer_time_zone') . '"';
    }

    /**
     * @param $requestType
     * @param $endpoint
     * @param bool $preferedTimeZone
     * @return \Microsoft\Graph\Http\GraphRequest
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function createRequest($requestType, $endpoint, $preferedTimeZone = False)
    {
        $this->setTokenGraph();
        $request = $this->graph->createRequest($requestType, $endpoint);
        if ($preferedTimeZone) {
            $request->addHeaders(["Prefer" => $this->getPreferTimeZone()]);
        }

        return $request;
    }

    /**
     * @param $requestType
     * @param $endpoint
     * @param bool $preferedTimeZone
     * @return \Microsoft\Graph\Http\GraphCollectionRequest
     * @throws \Microsoft\Graph\Exception\GraphException
     */
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