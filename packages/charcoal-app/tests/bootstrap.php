<?php

session_start();
mb_internal_encoding('UTF-8');
date_default_timezone_set('UTC');

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
