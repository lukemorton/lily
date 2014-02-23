<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Util;

use Lily\Util\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function requestProvider()
    {
        return array(
            array('/', NULL),
            array('/', array()),
            array('/', array('User-Agent' => 'Lily')),
        );
    }

    /**
     * @dataProvider requestProvider
     */
    public function testRequest($uri, $headers)
    {
        $method = 'GET';

        if ($headers === NULL)
        {
            $request = Request::request($method, $uri);
            $headers = array();
        }
        else
        {
            $request = Request::request($method, $uri, $headers);
        }

        $this->assertSame(compact('method', 'uri', 'headers'), $request);
    }

    /**
     * @dataProvider requestProvider
     */
    public function testGETRequest($uri, $headers)
    {
        if ($headers === NULL)
        {
            $request = Request::get($uri);
            $headers = array();
        }
        else
        {
            $request = Request::get($uri, $headers);
        }

        $this->assertSame(
            array('method' => 'GET') + compact('uri', 'headers'),
            $request);
    }

    /**
     * @dataProvider requestProvider
     */
    public function testPOSTRequest($uri, $headers)
    {
        if ($headers === NULL)
        {
            $request = Request::post($uri);
            $headers = array();
        }
        else
        {
            $request = Request::post($uri, $headers);
        }

        $this->assertSame(
            array('method' => 'POST') + compact('uri', 'headers'),
            $request);
    }
}
