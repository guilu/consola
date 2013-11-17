<?php
/**
 * Created by PhpStorm.
 * User: diegobarrioh
 * Date: 14/11/13
 * Time: 18:05
 */

namespace diegobarrioh\Console\Command;


use diegobarrioh\Console\Util\Ftp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class CajerosCommand
 *
 * @package DiegobarriohConsoleCommand
 */
class CajerosCommand extends Command
{

    const FICHERO_VERSION = "C:/SolidoApp/VERSAPP.TXT";
    const CAJERO_USER = "SISTEL";
    const CAJERO_PASS = "SISTEL";

    /**
     *  Nothing to show in here...
     */
    protected function configure()
    {
        $this
            ->setName('cajero:version')
            ->setDescription('Descarga un fichero de version por ftp del cajero')
            ->addArgument(
                'ip',
                InputArgument::REQUIRED,
                'La ip del cajero del que quieres descargar la versión'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //obtener los argumentos del commando
        $host = $input->getArgument('ip');
        //poner los parámetros del ftp
        $ftpParameters = array(
            "host"=> $host,
            "user"=> $this::CAJERO_USER,
            "pass"=> $this::CAJERO_PASS
        );
        //obtener el fichero
        $datos = $this->getVersionFile($ftpParameters);

        $output->writeln($datos);


    }

    /**
     * @param array $ftpParameters
     *
     * @return bool|string
     * @throws \Exception
     */
    private function getVersionFile($ftpParameters)
    {
        $ftp = new Ftp();
        $ftp->setFtpParameters($ftpParameters);
        $fichero = $this::FICHERO_VERSION;

        if ($ftp->connectAndLogin()) {
            if ($ftp->exists($fichero)) {
                $contenido = $ftp->get_contents($fichero, FTP_ASCII);

                return $contenido;
            } else {
                throw new \Exception("El fichero ".$this::FICHERO_VERSION." no existe en el host ".$ftpParameters["host"]);
            }
        } else {
            throw new \Exception("No me puedo conectar...");
        }
    }

} 