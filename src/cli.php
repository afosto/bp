#!/usr/bin/env php
<?php

require_once '../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Afosto\Bp\Helpers\DocGen\Generator;

$application = new Application();
$application->add(new Generator());
$application->run();