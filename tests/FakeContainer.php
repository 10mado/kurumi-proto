<?php
declare(strict_types=1);

namespace Kurumi\Tests;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\ServerRequest as ZendRequest;

class FakeContainer implements ContainerInterface
{
    public function has($id): bool
    {
        return true;
    }

    public function get($id)
    {
        if ($id === Request::class) {
            return new ZendRequest();
        }
        return new FakeMiddleware($id);
    }
}
