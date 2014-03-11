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

use Lily\Middleware\Session;

use Lily\Util\Request as Req;
use Lily\Util\Response as Res;

use Lily\Mock\MockSessionStore;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    private function wrapWithSessionMiddleware($store, $handler)
    {
        $mw = new Session(compact('store'));
        return $mw($handler);
    }

    private function callHandler($handler)
    {
        return $handler(array());
    }

    public function testItShouldAddArrayToSession()
    {
        $store = new MockSessionStore;
        $expectedValue = 'some value';

        $this->callHandler(
            $this->wrapWithSessionMiddleware(
                $store,
                function ($request) use (& $expectedValue) {
                    return array(
                        'session' => array('a' => $expectedValue),
                    );
                }));

        $this->assertSame($expectedValue, $store->session['a']);
    }

    public function testItShouldOverwriteArrayKeyInSession()
    {
        $store = new MockSessionStore;
        $store->session['b'] = 'initial value';
        $expectedValue = 'overwritten value';

        $this->callHandler(
            $this->wrapWithSessionMiddleware(
                $store,
                function ($request) use (& $expectedValue) {
                    return array(
                        'session' => array('b' => $expectedValue),
                    );
                }));

        $this->assertSame($expectedValue, $store->session['b']);
    }

    public function testItShouldNotOverwriteOtherKeys()
    {
        $expectedValue = 'other key value';
        $store = new MockSessionStore;
        $store->session['a'] = $expectedValue;

        $this->callHandler(
            $this->wrapWithSessionMiddleware(
                $store,
                function ($request) {
                    return array(
                        'session' => array('b' => 'unrelated'),
                    );
                }));

        $this->assertSame($expectedValue, $store->session['a']);
    }

    public function testItShouldGetArrayFromSession()
    {
        $expectedValue = 'something';
        $actualValue = NULL;
        $store = new MockSessionStore;
        $store->session['c'] = $expectedValue;

        $this->callHandler(
            $this->wrapWithSessionMiddleware(
                $store,
                function ($request) use (& $actualValue) {
                    $actualValue = $request['session']['c'];
                }));

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testItShouldAlwaysAddSessionKey()
    {
        $sessionKeyIsSet = FALSE;

        $this->callHandler(
            $this->wrapWithSessionMiddleware(
                new MockSessionStore,
                function ($request) use (& $sessionKeyIsSet) {
                    $sessionKeyIsSet = isset($request['session']);
                }));

        $this->assertTrue($sessionKeyIsSet);
    }

    public function testItShouldRemoveNullValuesFromSession()
    {
        $store = new MockSessionStore;
        $store->session = array('key' => TRUE);

        $this->callHandler(
            $this->wrapWithSessionMiddleware(
                $store,
                function ($request) use (& $sessionKeyIsSet) {
                    return array(
                        'session' => array('key' => NULL),
                    );
                }));

        $this->assertArrayNotHasKey('key', $store->session);
    }
}
