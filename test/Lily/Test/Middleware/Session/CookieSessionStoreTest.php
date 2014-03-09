<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Session\Middleware;

use Lily\Test\Middleware\Session\SessionStoreTest;

use Lily\Middleware\Session;
use Lily\Middleware\Session\CookieStore;

use Lily\Util\Request as Req;
use Lily\Util\Response as Res;

class CookieSessionStoreTest extends SessionStoreTest
{
    protected function store()
    {
        return new CookieStore(array(
            'cookie' => array('name' => 'test'),
        ));
    }

    protected function getFromStore($response, $key)
    {
        $cookie = $response['cookies']['test'];
        $store = json_decode($cookie['value'], TRUE);
        return $store[$key];
    }

    protected function addToStore($key, $value)
    {
        $session = array($key => $value);
        
        return array(
            'cookies' => array(
                'test' => json_encode($session),
            ),
            'session' => $session,
        );
    }
}
