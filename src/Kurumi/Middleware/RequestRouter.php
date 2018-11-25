<?php
declare(strict_types=1);

namespace Kurumi\Middleware;

use FastRoute\Dispatcher as FastRouteDispatcher;
use Kurumi\Exception\NotFoundException;
use Kurumi\Exception\MethodNotAllowedException;
use Kurumi\Routes;
use Middleland\Dispatcher as MiddlewareDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RequestRouter implements MiddlewareInterface
{
    private $container;

    private $routes;

    public function __construct(ContainerInterface $container, Routes $routes)
    {
        $this->container = $container;
        $this->routes = $routes;
    }

    public function process(Request $request, RequestHandler $next): Response
    {
        // get route via FastRoute
        $fastRouteDispatcher = $this->routes->getFastRouteDispatcher();
        $route = $fastRouteDispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($route[0] === FastRouteDispatcher::NOT_FOUND) {
            throw new NotFoundException();  // 404
        } elseif ($route[0] === FastRouteDispatcher::METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException();  // 405
        }

        // set attributes
        foreach ($route[2] as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        // middlewares
        $middlewares = $route[1];

        // dispatch route middlewares
        $dispatcher = new MiddlewareDispatcher($middlewares, $this->container);
        $response = $dispatcher->dispatch($request);

        return $response;
    }
}
