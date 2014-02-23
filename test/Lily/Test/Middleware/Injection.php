<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Middleware;

use Lily\Middleware\Injection;
use Lily\Util\Response;

class InjectionTest extends \PHPUnit_Framework_TestCase
{
    public function testInjection()
    {
        $actualValue = FALSE;

        $handler = function ($request) use (& $actual) {
            $actual = $request['injected-value'];
        };

        $expected = 'cool';
        $inject = array('injected-value' => $expected);
        $mw = new Injection(compact('inject'));
        $wrappedHandler = $mw($handler);
        $wrappedHandler(array());

        $this->assertSame($expected, $actual);
    }
}
