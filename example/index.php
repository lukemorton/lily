<?php

namespace Lily\Example;

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('Lily\Example\\', __DIR__.'/src');

use Lily\Adapter\HTTP;
use Lily\Example\Application\MainApplication;

(new HTTP)->run(new MainApplication);
