<?php
declare(strict_types=1);

namespace Kurumi\Tests;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class FakeMiddleware implements MiddlewareInterface
{
    private $char;

    public function __construct(string $char = '')
    {
        $this->char = $char;
    }

    public function process(Request $request, RequestHandler $next): Response
    {
        $response = $next->handle($request);
        $response->getBody()->write($this->char);
        return $response;
    }
}
