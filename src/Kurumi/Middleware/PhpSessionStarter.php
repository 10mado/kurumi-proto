<?php
declare(strict_types=1);

namespace Kurumi\Middleware;

use Kurumi\Config;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use RuntimeException;

class PhpSessionStarter implements MiddlewareInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(Request $request, RequestHandler $next): Response
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new RuntimeException('PHP sessions are disabled.');
        }

        $defaultSettings = [
            'lifetime' => '20 minutes',
            'path' => '/',
            'domain' => $request->getUri()->getHost(),
            'secure' => false,
            'httponly' => false,
            'name' => 'app-session',
            'autorefresh' => false,
        ];

        $config = [];
        if ($this->container->has(Config::class)) {
            $config = $this->container->get(Config::class);
        }
        $settings = array_merge($defaultSettings, ($config['session'] ?? []));

        $lifetime = $settings['lifetime'];
        if (is_string($lifetime)) {
            $lifetime = strtotime($lifetime) - time();
        }

        session_set_cookie_params(
            $lifetime,
            $settings['path'],
            $settings['domain'],
            $settings['secure'],
            $settings['httponly']
        );

        $name = $settings['name'] ?? session_name();
        session_name($name);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            $cookies = $request->getCookieParams();
            if ($settings['autorefresh'] && isset($cookies[$name])) {
                setcookie(
                    $name,
                    $cookies[$name],
                    time() + $lifetime,
                    $settings['path'],
                    $settings['domain'],
                    $settings['secure'],
                    $settings['httponly']
                );
            }
        }

        $response = $next->handle($request);

        if (session_status() === PHP_SESSION_ACTIVE && session_name() === $name) {
            session_write_close();
        }

        return $response;
    }
}
