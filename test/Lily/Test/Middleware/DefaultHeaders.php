<?php

namespace Lily\Test\Middleware;

use Lily\Middleware\DefaultHeaders;
use Lily\Util\Response;

class DefaultHeadersTest extends \PHPUnit_Framework_TestCase
{
    public function headerProvider()
    {
        $headersSetByHandler = array(
            'Content-Type' => 'text/plain',
            'Content-Length' => 100,
        );

        return array(
            array($headersSetByHandler, 'Content-Type', 'text/html', FALSE),
            array($headersSetByHandler, 'Content-Length', 200, FALSE),
            array($headersSetByHandler, 'X-Another-One', 'yay', TRUE),
        );
    }

    /**
     * @dataProvider headerProvider
     */
    public function testDefaultHeadersAreApplied(
        $headersSetByHandler,
        $header,
        $value,
        $expected)
    {
        $handler = function ($request) use ($headersSetByHandler) {
            return Response::ok('', $headersSetByHandler);
        };

        $headers = array($header => $value);
        $mw = new DefaultHeaders(compact('headers'));
        $wrappedHandler = $mw($handler);
        $response = $wrappedHandler(array());

        $this->assertSame($expected, $response['headers'][$header] === $value);
    }
}
