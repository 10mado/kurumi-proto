<?php
declare(strict_types=1);

namespace Kurumi\Tests;

use Kurumi\Dispatcher;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class DispatcherTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testAction()
    {
        $container = new FakeContainer();
        $dispatcher = new Dispatcher($container);

        ob_start();
        $dispatcher->dispatch([
            new FakeAction(),
        ]);
        $headers = headers_list();
        $res = ob_get_clean();

        $this->assertEquals(0, count($headers));
        $this->assertEquals('', $res);
    }
}
