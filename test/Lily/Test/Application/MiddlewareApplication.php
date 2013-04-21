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

        $handler =
            new MiddlewareApplication(array(
                new Application(function () use (& $calledOrder) {
                    $calledOrder[] = 3;
                }),
                new Middleware(function () use (& $calledOrder) {
                    $calledOrder[] = 2;
                }),
                new Middleware(function () use (& $calledOrder) {
                    $calledOrder[] = 1;
                }),
            ));
        $handler(array());

        $this->assertSame($expectedCalledOrder, $calledOrder);
    }

    public function testWithoutMiddleware()
    {
        $called = FALSE;

        $handler =
            new MiddlewareApplication(array(
                new Application(function () use (& $called) {
                    $called = TRUE;
                }),
            ));
        $handler(array());

        $this->assertTrue($called);
    }
}
