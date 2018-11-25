<?php
declare(strict_types=1);

namespace Kurumi;

use Middleland\Dispatcher as MiddlewareDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class Dispatcher
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function dispatch(array $middlewares): void
    {
        if (! $this->container->has(Request::class)) {
            throw new RuntimeException('Container must have ServerRequestInterface::class');
        }

        $request = $this->container->get(Request::class);

        // dispatch middlewares
        $dispatcher = new MiddlewareDispatcher($middlewares, $this->container);
        $response = $dispatcher->dispatch($request);

        // output
        (new SapiEmitter())->emit($response);
    }
}
