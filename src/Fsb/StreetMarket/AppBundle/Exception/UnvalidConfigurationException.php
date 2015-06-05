<?php

namespace Fsb\StreetMarket\AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * UnvalidConfigurationException
 *
 * Indicates the current app configuration is unvalid. Some parameters required by the controllers are missing.
 */
class UnvalidConfigurationException extends HttpException
{
    public function __construct($message = null, Exception $previous = null, array $headers = array())
    {
        parent::__construct(500, $message, $previous, $headers, 1);

        return $this;
    }
}