<?php
declare(strict_types=1);

namespace Kurumi\Tests;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Zend\Diactoros\Response\HtmlResponse;

class FakeAction implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $next): Response
    {
        return new HtmlResponse('');
    }
}
