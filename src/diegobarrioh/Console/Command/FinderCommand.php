<?php
/**
 * Created by PhpStorm.
 * User: diegobarrioh
 * Date: 14/11/13
 * Time: 14:02
 */

namespace diegobarrioh\Console\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;


/**
 * Class FinderCommand
 *
 * @package DiegobarriohConsoleCommand
 */
class FinderCommand extends Command
{
    /**
     *  Nothing to show in here...
     */
    protected function configure()
    {
        $this
            ->setName('find:numfiles')
            ->setDescription('Obten el numero de ficheros de un directorio. default __DIR__')
            ->addArgument(
                'directorio',
                InputArgument::OPTIONAL,
                'De que directorio quieres saber el numero de ficheros'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $dir = $input->getArgument('directorio');

        $salida = $this->findNumFiles($dir);

        $output->writeln("numero de ficheros:".$salida);
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    private function findNumFiles($dir)
    {
        $numFiles = 0;
        $finder = new Finder();

        if ($dir == "") {
            $dir = __DIR__;
        }

        $f = $finder->files()->followLinks()->depth(0)->in($dir);

        foreach ($f as $fichero) {
            /** @var SplFileInfo $fichero */
            print $fichero->getFilename()."\n";
            $numFiles++;
        }

        return $numFiles;
    }
} 