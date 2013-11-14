<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

//creation of the application
$console = new Application();

//add the commands here
$console->add(new \diegobarrioh\Console\Command\AcmeCommand());

//get things done!
$console->run();