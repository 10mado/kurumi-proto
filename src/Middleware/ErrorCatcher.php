<?php
declare(strict_types=1);

namespace Kurumi\Middleware;

use Kurumi\Exception\ErrorException;
use Kurumi\Exception\ForbiddenException;
use Kurumi\Exception\NotFoundException;
use Kurumi\Exception\MethodNotAllowedException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface as Logger;
use RuntimeException;
use Throwable;
use Zend\Diactoros\Response\EmptyResponse;

class ErrorCatcher implements MiddlewareInterface
{
    const ERROR_HANDLER = 'errorHandler';
    const FORBIDDEN_HANDLER = 'forbiddenHandler';
    const NOT_FOUND_HANDLER = 'notFoundHandler';
    const METHOD_NOT_ALLOWED_HANDLER = 'methodNotAllowedHandler';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(Request $request, RequestHandler $next): Response
    {
        $response = null;
        try {
            $response = $next->handle($request);
        } catch (Throwable $e) {
            $response = $this->handleError($request, $e);
        }
        return $response;
    }

    private function handleError(Request $request, Throwable $e): Response
    {
        if ($e instanceof ErrorException) {
            $status = $e->getStatusCode();
        }

        if ($e instanceof ForbiddenException) {
            $status = $status ?? 403;
            $handler = self::FORBIDDEN_HANDLER;
        } elseif ($e instanceof NotFoundException) {
            $status = $status ?? 404;
            $handler = self::NOT_FOUND_HANDLER;
        } elseif ($e instanceof MethodNotAllowedException) {
            $status = $status ?? 405;
            $handler = self::METHOD_NOT_ALLOWED_HANDLER;
        } else {
            // other exceptions
            $status = $status ?? 500;
            $handler = self::ERROR_HANDLER;
            if ($this->container->has(Logger::class)) {
                // logging
                $logger = $this->container->get(Logger::class);
                $logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
            }
        }

        if ($this->container->has($handler)) {
            $callback = $this->container->get($handler);
            if (is_callable($callback)) {
                $response = call_user_func_array($callable, [$request, $e]);
                if (! ($response instanceof Response)) {
                    throw new RuntimeException('Error handler should return response.', 0, $e);
                }
                return $response;
            }
        }

        return (new EmptyResponse($status));
    }
}
