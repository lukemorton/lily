<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Middleware;

use Lily\Middleware\ExceptionHandler;

use Exception;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultHandler()
    {
        $exceptionHandler = new ExceptionHandler;

        $wrappedHandler =
            $exceptionHandler(
                function ($request) {
                    throw new Exception;
                });

        $response = $wrappedHandler(array());
        $this->assertSame(500, $response['status']);
        $this->assertNotEmpty($response['body']);
    }
    
    public function testCustomHandler()
    {
        $expectedException = new Exception;
        $actualException = NULL;

        $exceptionHandler =
            new ExceptionHandler(array(
                'handler' => function ($request) use (& $actualException) {
                    $actualException = $request['exception'];
                },
            ));

        $wrappedHandler =
            $exceptionHandler(
                function ($request) use ($expectedException) {
                    throw $expectedException;
                });

        $wrappedHandler(array());

        $this->assertSame($expectedException, $actualException);
    }
}
