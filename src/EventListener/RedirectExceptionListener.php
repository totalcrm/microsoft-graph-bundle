<?php

namespace TotalCRM\MicrosoftGraph\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use TotalCRM\MicrosoftGraph\Exception\RedirectException;

/**
 * Class RedirectExceptionListener
 * @package TotalCRM\MicrosoftGraph\EventListener
 */
class RedirectExceptionListener
{
    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event instanceof RedirectException) {
            $event->setResponse($event->getException()->getRedirectResponse());
        }
    }
}