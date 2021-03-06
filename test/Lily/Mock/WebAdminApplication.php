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

class WebAdminApplication extends \Lily\Application\WebApplication
{
    protected function routes()
    {
        return array(
            'index' => array('GET', '/admin', 'admin'),
        );
    }
}
