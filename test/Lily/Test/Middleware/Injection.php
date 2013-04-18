<?php

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
        $mw = new Injection(array('injected-value' => $expected));
        $wrappedHandler = $mw->wrapHandler($handler);
        $wrappedHandler(array());

        $this->assertSame($expected, $actual);
    }
}
