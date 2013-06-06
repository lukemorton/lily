<?php

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Lily\Test', __DIR__);
$loader->add('Lily\Mock', __DIR__);

session_start();
