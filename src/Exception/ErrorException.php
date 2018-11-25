<?php
declare(strict_types=1);

namespace Kurumi\Exception;

class ErrorException extends \Exception
{
    protected $statusCode = 500;

    /**
     * Get status code of HTTP error.
     *
     * @return integer
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
