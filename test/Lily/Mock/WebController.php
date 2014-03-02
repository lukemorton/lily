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

class WebController
{
    public function index($request)
    {
        return 'index';
    }

    public function slug($request)
    {
        return $request['params']['slug'];
    }
}
