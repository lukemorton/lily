<?php

namespace Lily\Test\Application;

use Lily\Application\MiddlewareApplication;
use Lily\Mock\Application;
use Lily\Mock\Middleware;

class MiddlewareApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testMiddlewareOrder()
    {
        $expectedCalledOrder = array(1, 2, 3);
        $calledOrder = array();

        $container =
            new MiddlewareApplication(
                new Application(function () use (& $calledOrder) {
                    $calledOrder[] = 3;
                }),
                array(
                    new Middleware(function () use (& $calledOrder) {
                        $calledOrder[] = 1;
                    }),
                    new Middleware(function () use (& $calledOrder) {
                        $calledOrder[] = 2;
                    }),
                ));

        $handler = $container->handler();
        $handler(array());

        $this->assertSame($expectedCalledOrder, $calledOrder);
    }

    public function testWithoutMiddleware()
    {
        $called = FALSE;

        $container =
            new MiddlewareApplication(
                new Application(function () use (& $called) {
                    $called = TRUE;
                }));

        $handler = $container->handler();
        $handler(array());

        $this->assertTrue($called);
    }
}
