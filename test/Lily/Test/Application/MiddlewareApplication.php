<?php

namespace Lily\Test\Application;

use Lily\Application\MiddlewareApplication;

use Lily\Util\Response;

class MiddlewareApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testMiddlewareOrder()
    {
        $expectedCalledOrder = array(1, 2, 3);
        $calledOrder = array();

        $handler =
            new MiddlewareApplication(array(
                function ($request) use (& $calledOrder) {
                    $calledOrder[] = 3;
                    return Response::ok();
                },
                
                function ($handler) use (& $calledOrder) {
                    return function ($request) use ($handler, & $calledOrder) {
                        $calledOrder[] = 2;
                        return $handler($request);
                    };
                },

                function ($handler) use (& $calledOrder) {
                    return function ($request) use ($handler, & $calledOrder) {
                        $calledOrder[] = 1;
                        return $handler($request);
                    };
                },
            ));
        $handler(array());

        $this->assertSame($expectedCalledOrder, $calledOrder);
    }

    public function testWithoutMiddleware()
    {
        $called = FALSE;

        $handler =
            new MiddlewareApplication(array(
                function () use (& $called) {
                    $called = TRUE;
                },
            ));
        $handler(array());

        $this->assertTrue($called);
    }
}
