<?php

namespace TotalCRM\MicrosoftGraph\Manager;

use RuntimeException;
use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphRequest;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Cursor;

use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Http\GraphCollectionRequest;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Model;
use Exception;

/**
 * Class SubscriptionsManager
 * @package TotalCRM\MicrosoftGraph\DependencyInjection
 */
class SubscriptionsManager
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
     * @return Model\Subscription[]|mixed
     * @throws Exception
     */
    public function getSubscriptions()
    {
        return $this->request
            ->createCollectionRequest('GET', '/subscriptions')
            ->setReturnType(Model\Subscription::class)
            ->execute();
    }

    /**
     * Create an Subscription
     * @param Model\Subscription $subscription
     * @return mixed|array|void
     * @throws Exception
     */
    public function addSubscription(Model\Subscription $subscription = null)
    {
        if ($subscription === null) {
            throw new RuntimeException("Your Subscription is null");
        }

        $request = $this->request
            ->createRequest('POST', '/subscriptions')
            ->attachBody($subscription->jsonSerialize())
            ->setReturnType(Model\Subscription::class)
        ;
        
        return $request->execute();
    }

    /**
     * Update an Subscription
     * @param Model\Subscription|null $subscription
     * @return mixed|array|void
     * @throws Exception
     */
    public function updateSubscription(?Model\Subscription $subscription = null)
    {
        if ($subscription === null) {
            throw new RuntimeException("Your Subscription is null");
        }

        $request = $this->request
            ->createRequest('PATCH', '/subscriptions/' . $subscription->getId())
            ->attachBody($subscription->jsonSerialize())
            ->setReturnType(Model\Subscription::class)
        ;

        return $request->execute();
    }

    /**
     * Delete an Subscription
     * @param $id
     * @return mixed|array
     * @throws Exception
     */
    public function deleteSubscription($id = null)
    {
        if ($id === null) {
            throw new RuntimeException("Subscription id is null");
        }

        return $this->request
            ->createRequest('DELETE', '/subscriptions/' . $id)
            ->execute();
    }
}
