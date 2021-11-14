<?php

namespace TotalCRM\MicrosoftGraph\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use TotalCRM\MicrosoftGraph\Exception\RedirectException;

/**
 * Class RedirectExceptionListener
 * @package TotalCRM\MicrosoftGraph\EventListener
 */
class RedirectExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof RedirectException) {
            $event->setResponse($event->getException()->getRedirectResponse());
        }
    }
}