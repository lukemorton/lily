<?php

namespace Lily\Test\Middleware;

use Lily\Middleware\Session;
use Lily\Middleware\Session\CookieStore;

use Lily\Util\Request as Req;
use Lily\Util\Response as Res;

class CookieSessionStoreTest extends \PHPUnit_Framework_TestCase
{
    public function testWriteToSession()
    {
        $expectedSession = array('a' => 1);
        
        $mw = new Session(array(
            'store' => new CookieStore(array(
                'cookie' => array('name' => 'test'),
            )),
        ));

        $wrappedHandler =
            $mw(
                function () use ($expectedSession) {
                    $session = $expectedSession;
                    return Res::ok() + compact('session');
                });

        $response = $wrappedHandler(Req::get('/'));

        $this->assertSame(
            json_encode($expectedSession),
            $response['cookies']['test']['value']);
    }

    public function testReadSession()
    {
        $expectedSession = array('b' => 2);

        $actualSession = NULL;

        $mw = new Session(array(
            'store' => new CookieStore(array(
                'cookie' => array('name' => 'test'),
            )),
        ));

        $wrappedHandler =
            $mw(
                function ($request) use (& $actualSession) {
                    $actualSession = $request['session'];
                    return Res::ok();
                });

        $cookies = array('test' => json_encode($expectedSession));
        $wrappedHandler(Req::get('/') + compact('cookies'));

        $this->assertSame($expectedSession, $actualSession);
    }
}
