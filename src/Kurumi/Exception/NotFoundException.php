<?php
declare(strict_types=1);

namespace Kurumi\Exception;

class NotFoundException extends ErrorException
{
    protected $statusCode = 404;
}
