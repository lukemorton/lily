<?php

$loader = require __DIR__.'/../../vendor/autoload.php';
$loader->addPsr4('Lily\Example\\', __DIR__.'/../src');

use Lily\Adapter\Test;

use Lily\Middleware as MW;

use Lily\Util\Request;

function runApplication($handler, $request)
{
    $testAdapter = new Test(array(
        'followRedirect' => TRUE,
        'persistCookies' => TRUE,
    ));
    return $testAdapter->run(compact('handler', 'request'));
}

function applicationResponse($application, $url, $request = array())
{
    return runApplication($application, $request + Request::get($url));
}

function applicationFormResponse($application, $url)
{
    return runApplication($application, Request::post($url));
}

function authedCookie()
{
    $headers = array('user-agent' => 'Lily\Adapter\Test');
    return MW\Cookie::sign(compact('headers'), 'authed', TRUE, 'random');
}

function authedCookieRequest()
{
    return array(
        'headers' => array(
            'cookies' => array('authed' => authedCookie()),
        ),
    );
}
