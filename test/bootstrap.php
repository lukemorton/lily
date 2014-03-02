<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Lily\Test', __DIR__);
$loader->add('Lily\Mock', __DIR__);

session_start();
