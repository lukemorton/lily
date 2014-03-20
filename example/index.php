<?php

namespace Lily\Example;

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('Lily\Example\\', __DIR__.'/src');

use Lily\Adapter\HTTP;
use Lily\Example\Interaction\Container\MainContainer;

$container = new MainContainer;
$handler = $container->application();
(new HTTP)->run(compact('handler'));
