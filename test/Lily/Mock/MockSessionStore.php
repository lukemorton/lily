<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Mock;

use Lily\Application\RoutedApplication;

class MockSessionStore
{
    public $session = array();

    public function set($response)
    {
        if (isset($response['session'])) {
            $this->session = $response['session'];
        }

        return $response;
    }

    public function get($request)
    {
        $request['session'] = $this->session;
        return $request;
    }
}
