<?php
declare(strict_types=1);

namespace Kurumi\Middleware;

use Kurumi\Session\CsrfGuard;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CsrfTokenVerifier implements MiddlewareInterface
{
    private $container;

    private $csrfGuard;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(Request $request, RequestHandler $next): Response
    {
        $this->makeCsrfGuardAvailable();
        $requestMethod = $request->getMethod();
        if (in_array($requestMethod, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $body = $request->getParsedBody();
            $token = $this->getTokenFromRequest($request);
            if (is_null($token) || ! $this->csrfGuard->verify($token)) {
                $failureCallable = $this->csrfGuard->getFailureCallable();
                return $failureCallable($request);
            }
        }
        return $next->handle($request);
    }

    private function makeCsrfGuardAvailable(): void
    {
        if ($this->container->has(CsrfGuard::class)) {
            $this->csrfGuard = $this->container->get(CsrfGuard::class);
        }
        if (is_null($this->csrfGuard)) {
            $this->csrfGuard = new CsrfGuard();
        }
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        $token = null;
        // from request body
        $body = $request->getParsedBody();
        $token = $body[$this->csrfGuard->getTokenInputItemName()] ?? null;
        if (is_null($token)) {
            // from request header
            $token = $request->getHeader($this->csrfGuard->getTokenHeaderName());
            $token = array_shift($token);
        }
        return $token;
    }
}
