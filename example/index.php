<?php

namespace Lily\Example;

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('Lily\Example\\', __DIR__.'/src');

use Lily\Adapter\HTTP;
use Lily\Example\Application\FrontEndApplication;

(new HTTP)->run(new FrontEndApplication);
