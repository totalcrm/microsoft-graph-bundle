<?php

namespace TotalCRM\MicrosoftGraph\Exception;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Exception;

/**
 * Class RedirectException
 * @package TotalCRM\MicrosoftGraph\Exception
 */
class RedirectException extends Exception
{
    private RedirectResponse $redirectResponse;

    /**
     * RedirectException constructor.
     * @param RedirectResponse $redirectResponse
     * @param string|null $message
     * @param int|null $code
     * @param Exception|null $previousException
     */
    public function __construct(RedirectResponse $redirectResponse, ?string $message = '', ?int $code = 0, Exception $previousException = null)
    {
        $this->redirectResponse = $redirectResponse;
        parent::__construct($message, $code, $previousException);
    }

    /**
     * @return RedirectResponse
     */
    public function getRedirectResponse(): RedirectResponse
    {
        return $this->redirectResponse;
    }
}