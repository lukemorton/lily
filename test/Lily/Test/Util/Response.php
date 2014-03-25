<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Util;

use Lily\Util\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function responseProvider()
    {
        return array(
            array(200, array('Content-Type' => 'text/plain'), 'OK'),
            array(500, array('Content-Type' => 'text/html'), 'Error'),
        );
    }

    /**
     * @dataProvider responseProvider
     */
    public function testResponse($status, $headers, $body)
    {
        $this->assertSame(
            compact('status', 'headers', 'body'),
            Response::response($status, $body, $headers));
    }

    /**
     * @dataProvider responseProvider
     */
    public function testOKResponse($status, $headers, $body)
    {
        $status = 200;

        $this->assertSame(
            compact('status', 'headers', 'body'),
            Response::ok($body, $headers));
    }

    /**
     * @dataProvider responseProvider
     */
    public function testNotFoundResponse($status, $headers, $body)
    {
        $status = 404;

        $this->assertSame(
            compact('status', 'headers', 'body'),
            Response::notFound($body, $headers));
    }

    public function testRedirectResponse()
    {
        $status = 302;
        $headers = array('Location' => '/');
        $body = '';

        $this->assertSame(
            compact('status', 'headers', 'body'),
            Response::redirect($headers['Location']));
    }

    public function testRedirectCustomStatusResponse()
    {
        $status = 301;
        $headers = array('Location' => '/');
        $body = '';

        $this->assertSame(
            compact('status', 'headers', 'body'),
            Response::redirect($headers['Location'], $status));
    }
}
