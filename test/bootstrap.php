<?php

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Lily\Test', __DIR__);
$loader->add('Lily\Mock', __DIR__);
$loader->addPsr4('Lily\Example\\', __DIR__.'/../example/src');

session_start();
