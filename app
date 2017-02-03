#!/usr/bin/env php
<?php

namespace DSiepmann;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new Command\FakeMysqlCommand());
$application->run();
