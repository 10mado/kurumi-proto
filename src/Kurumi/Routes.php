<?php
declare(strict_types=1);

namespace Kurumi;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Server\MiddlewareInterface;
use function FastRoute\simpleDispatcher;

class Routes
{
    private $routes = [];

    /**
     * Add a route.
     *
     * @param string|array $methods
     * @param string $path
     * @param string|array $handlers
     * @return void
     */
    public function add($methods, string $path, $handlers): void
    {
        $methods = array_map('strtoupper', (array) $methods);
        $this->routes[] = [
            'methods' => (array) $methods,
            'path' => $path,
            'handlers' => (array) $handlers,
        ];
    }

    /**
     * Get dispatcher instance of FastRoute
     *
     * @return Dispatcher
     */
    public function getFastRouteDispatcher(): Dispatcher
    {
        $routes = $this->routes;
        $fastRouteDispatcher = simpleDispatcher(function (RouteCollector $r) use ($routes) {
            foreach ($routes as $route) {
                $r->addRoute($route['methods'], $route['path'], $route['handlers']);
            }
        });
        return $fastRouteDispatcher;
    }
}
