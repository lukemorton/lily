<?php

namespace Lily\Test\Middleware;

use Lily\Middleware\Session;

use Lily\Util\Request as Req;
use Lily\Util\Response as Res;

use Lily\Mock\MockSessionStore;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testWriteToSession()
    {
        $store = new MockSessionStore;

        $expectedSession = array('a' => 1);

        $mw = new Session(compact('store'));

        $wrappedHandler =
            $mw(
                function () use ($expectedSession) {
                    $session = $expectedSession;
                    return Res::ok() + compact('session');
                });

        $wrappedHandler(Req::get('/'));

        $this->assertSame($expectedSession, $store->session);
    }

    public function testReadSession()
    {
        $store = new MockSessionStore;
        $store->session = array('b' => 2);

        $actualSession = NULL;

        $mw = new Session(compact('store'));
        
        $wrappedHandler =
            $mw(
                function ($request) use (& $actualSession) {
                    $actualSession = $request['session'];
                    return Res::ok();
                });

        $wrappedHandler(Req::get('/'));

        $this->assertSame($store->session, $actualSession);
    }
}
