<?php
declare(strict_types=1);

namespace Kurumi\View;

use Psr\Http\Message\ResponseInterface as Response;
use Twig_Environment;
use Twig_ExtensionInterface;
use Twig_Loader_Filesystem;
use Zend\Diactoros\Response\HtmlResponse;

class TwigView
{
    private $loader;
    private $environment;
    private $defaultData = [];

    public function __construct($path, array $settings = [])
    {
        $this->loader = $this->createLoader((array) $path);
        $this->environment = new Twig_Environment($this->loader, $settings);
    }

    public function addExtension(Twig_ExtensionInterface $extension): void
    {
        $this->environment->addExtension($extension);
    }

    public function fetch(string $template, array $data = []): string
    {
        $data = array_merge($this->defaultData, $data);
        return $this->environment->render($template, $data);
    }

    public function render(string $template, array $data = []): Response
    {
        $html = $this->fetch($template, $data);
        $response = new HtmlResponse($html);
        $response = $response->withHeader('Content-Length', strlen($html));
        return $response;
    }

    public function getEnvironment(): Twig_Environment
    {
        return $this->environment;
    }

    private function createLoader(array $paths): Twig_Loader_Filesystem
    {
        $loader = new Twig_Loader_Filesystem();
        foreach ($paths as $namespace => $path) {
            if (is_string($namespace)) {
                $loader->setPaths($path, $namespace);
            } else {
                $loader->addPath($path);
            }
        }
        return $loader;
    }
}
