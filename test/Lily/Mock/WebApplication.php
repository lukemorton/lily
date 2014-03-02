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

class WebApplication extends \Lily\Application\WebApplication
{
    protected function routes()
    {
        return array(
            'index' => array('GET', '/', array('test', 'index')),
            'slug' => array('GET', '/slug/:slug', array('test', 'slug')),
            'admin' => array('GET', '/admin', $this->application('admin')),
        );
    }
}
