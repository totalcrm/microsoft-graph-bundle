<?php

namespace TotalCRM\MicrosoftGraph\DependencyInjection;

use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphClient;

use Microsoft\Graph\Http\GraphCollectionRequest;
use Microsoft\Graph\Http\GraphRequest;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use DateTime;
use Exception;

/**
 * Class MicrosoftGraphRequest
 * @package TotalCRM\MicrosoftGraph\DependencyInjection
 */
class MicrosoftGraphRequest
{
    private MicrosoftGraphClient $client;
    private Graph $graph;

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
    public function setVersion($version = ""): void
    {
        if (in_array($version, ['v1.0', 'beta'])) {
            $this->graph->setApiVersion($version);
        } else {
            $version = $this->client->getConfig()['version'] ?? null;
            if (in_array($version, ['v1.0', 'beta'])) {
                $this->graph->setApiVersion();
            }
        }
    }

    /**
     * @return mixed|string
     * @throws Exception
     */
    public function getToken()
    {
        return $this->client->refreshToken()->getToken();
    }

    /**
     * @throws Exception
     */
    public function setTokenGraph(): void
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
    public function getPreferTimeZone(): string
    {
        return 'outlook.timezone="' . $this->getConfig('prefer_time_zone') . '"';
    }

    /**
     * @param $requestType
     * @param $endpoint
     * @param bool $preferedTimeZone
     * @return GraphRequest
     * @throws Exception
     */
    public function createRequest($requestType, $endpoint, $preferedTimeZone = false): GraphRequest
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
     * @return GraphCollectionRequest
     * @throws Exception
     */
    public function createCollectionRequest($requestType, $endpoint, $preferedTimeZone = false): GraphCollectionRequest
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
     * @return Model\DateTimeTimeZone
     */
    public function getDateTimeTimeZone(DateTime $date): Model\DateTimeTimeZone
    {
        $dateTime = $this->getDateMicrosoftFormat($date);
        $timezone = $this->getConfig('prefer_time_zone');

        return new Model\DateTimeTimeZone(['dateTime' => $dateTime, 'timezone' => $timezone]);
    }
}