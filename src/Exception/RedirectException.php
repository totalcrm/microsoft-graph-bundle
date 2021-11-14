<?php

namespace TotalCRM\MicrosoftGraph\Exception;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectException
 * @package TotalCRM\MicrosoftGraph\Exception
 */
class RedirectException extends Exception
{
    private $redirectResponse;

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
    public function getRedirectResponse()
    {
        return $this->redirectResponse;
    }
}