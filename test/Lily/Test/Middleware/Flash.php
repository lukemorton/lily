<?php

namespace Lily\Test\Middleware;

use Lily\Middleware\Flash;

use Lily\Util\Request as Req;
use Lily\Util\Response as Res;

class FlashTest extends \PHPUnit_Framework_TestCase
{
    public function testWriteFlashToSession()
    {
        $mw = new Flash;

        $flash = 'message';

        $wrappedHandler =
            $mw(
                function ($request) use ($flash) {
                    return Res::ok() + compact('flash');
                });

        $response = $wrappedHandler(Req::get('/'));
        $this->assertSame($flash, $response['session']['_flash']);
    }

    public function testReadFlashFromSession()
    {
        $mw = new Flash;

        $flash = 'message';

        $wrappedHandler =
            $mw(
                function ($request) use (& $actualRequest) {
                    $actualRequest = $request;
                    return Res::ok();
                });

        $response =
            $wrappedHandler(
                Req::get('/')
                + array(
                    'session' => array('_flash' => $flash),
                ));

        $this->assertSame($flash, $actualRequest['flash']);
    }
}
