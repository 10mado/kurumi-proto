<?php
declare(strict_types=1);

namespace Kurumi\Exception;

class ForbiddenException extends ErrorException
{
    protected $statusCode = 403;
}
