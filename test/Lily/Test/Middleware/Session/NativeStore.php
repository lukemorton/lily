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

use Lily\Middleware\Session;

use Lily\Util\Request as Req;
use Lily\Util\Response as Res;

class NativeSessionStoreTest extends \PHPUnit_Framework_TestCase
{
    public function testWriteToSession()
    {
        $expectedSession = array('a' => 1);
        
        $mw = new Session;

        $wrappedHandler =
            $mw(
                function () use ($expectedSession) {
                    $session = $expectedSession;
                    return Res::ok() + compact('session');
                });

        $wrappedHandler(Req::get('/'));

        $this->assertSame($expectedSession, $_SESSION);
    }

    public function testReadSession()
    {
        $_SESSION = array('b' => 2);

        $actualSession = NULL;

        $mw = new Session;

        $wrappedHandler =
            $mw(
                function ($request) use (& $actualSession) {
                    $actualSession = $request['session'];
                    return Res::ok();
                });

        $wrappedHandler(Req::get('/'));

        $this->assertSame($_SESSION, $actualSession);
    }
}
