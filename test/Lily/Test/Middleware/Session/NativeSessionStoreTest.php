<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Middleware\Session;

use Lily\Test\Middleware\Session\SessionStoreTest;

use Lily\Middleware\Session\NativeStore;
use Lily\Middleware\Session;

use Lily\Util\Request as Req;
use Lily\Util\Response as Res;

class NativeSessionStoreTest extends SessionStoreTest
{
    protected function store()
    {
        return new NativeStore;
    }

    protected function getFromStore($response, $key)
    {
        return $_SESSION[$key];
    }

    protected function addToStore($key, $value)
    {
        $_SESSION[$key] = $value;
        return array(
            'session' => array($key => $value),
        );
    }
}
