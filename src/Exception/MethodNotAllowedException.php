<?php
declare(strict_types=1);

namespace Kurumi\Exception;

class MethodNotAllowedException extends ErrorException
{
    protected $statusCode = 405;
}
