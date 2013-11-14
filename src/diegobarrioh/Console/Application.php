<?php

namespace diegobarrioh\Console;

use Symfony\Component\Console\Application as BaseApplication;

/**
 * Class Application
 * Think of the application as the container, which holds all commands and also provides the console with some default options, concerning output coloring, verbosity, interactivity,
 *
 * @package DiegobarriohConsole
 */
class Application extends BaseApplication
{
    const NAME = 'DiegoBarrioH\' Console Application';
    const VERSION = '1.0';

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        parent::__construct(static::NAME, static::VERSION);
    }
}