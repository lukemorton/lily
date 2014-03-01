<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Application;

use Lily\Application\MiddlewareApplication;

use Lily\Mock\MiddlewareApplicationWithMiddleware;

use Lily\Util\Response;

class MiddlewareApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testMiddlewareOrder()
    {
        $expectedCalledOrder = array(1, 2, 3);
        $calledOrder = array();

        $handler =
            new MiddlewareApplication(array(
                'handler' => function ($request) use (& $calledOrder) {
                    $calledOrder[] = 3;
                    return Response::ok();
                },
                
                'middleware' => array(
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
                ),
            ));
        $handler(array());

        $this->assertSame($expectedCalledOrder, $calledOrder);
    }

    public function testWithoutMiddleware()
    {
        $called = FALSE;

        $handler =
            new MiddlewareApplication(array(
                'handler' => function () use (& $called) {
                    $called = TRUE;
                },
            ));
        $handler(array());

        $this->assertTrue($called);
    }

    public function testItShouldUseMiddlewareProvidedInChildClass()
    {
        $handler = new MiddlewareApplicationWithMiddleware;
        $handler->m = 'test';
        $this->assertSame('test', $handler(array()));
    }
}
