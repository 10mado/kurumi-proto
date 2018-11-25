<?php
declare(strict_types=1);

namespace Kurumi\Tests;

use Kurumi\Dispatcher;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class DispatcherTest extends TestCase
{
    public function testAction()
    {
        $container = new FakeContainer();
        $dispatcher = new Dispatcher($container);
        $dispatcher->dispatch([
            new FakeAction(),
        ]);
    }
}
