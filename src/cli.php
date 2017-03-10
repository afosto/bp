#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use Afosto\Bp\Helpers\DocGen\Generator;

$application = new Application();

$generator = new Generator();
$generator->root = dirname(__FILE__);
$application->add($generator);
$application->run();