<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

//creation of the application
$console = new Application();

//add the commands here
$console->add(new \diegobarrioh\Console\Command\AcmeCommand());
$console->add(new \diegobarrioh\Console\Command\FinderCommand());
$console->add(new \diegobarrioh\Console\Command\CajerosCommand());
//get things done!
$console->run();