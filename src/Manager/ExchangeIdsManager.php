<?php


namespace TotalCRM\MicrosoftGraph\Manager;

use RuntimeException;
use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphRequest;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Http\GraphCollectionRequest;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Model;
use Exception;

class ExchangeIdsManager
{
    private MicrosoftGraphRequest $request;

    /**
     * ContactManager constructor.
     * @param MicrosoftGraphRequest $request
     */
    public function __construct(MicrosoftGraphRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param array $inputIds
     * @return Model\ExchangeIdFormat|mixed
     * @throws Exception
     */
    public function getTranslateExchangeIds(?array $inputIds = [])
    {
        $request = $this->request
            ->createRequest('POST', '/me/translateExchangeIds')
            ->attachBody([
                "inputIds" => $inputIds,
                "targetIdType" => "restImmutableEntryId",
                "sourceIdType" => "restId",
            ])
            ->setReturnType(Model\ExchangeIdFormat::class)
        ;

        return $request->execute();
    }

}